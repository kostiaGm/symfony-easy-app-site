<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

trait GetQueryBuilderRepositoryTrait
{
    use AliasRepositoryTrait;

    public function getQueryBuilder(?QueryBuilder $queryBuilder = null, string $indexBy = null): QueryBuilder
    {
        if ($queryBuilder === null) {
            $queryBuilder = $this->createQueryBuilder($this->getAlias(), $indexBy);
        }

        return $queryBuilder;
    }

    public function getQueryBuilderWithSiteId(
        ?int $siteId,
        ?QueryBuilder $queryBuilder = null,
        string $indexBy = null
    ) : QueryBuilder {
        $queryBuilder = $this->getQueryBuilder($queryBuilder, $indexBy);
        if ($siteId !== null) {
            $queryBuilder
                ->andWhere($this->getAlias().".siteId=:siteId")
                ->setParameter("siteId", $siteId)
            ;
        }
        return $queryBuilder;
    }

    public function getDataLength(int $siteId): int
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilderWithSiteId($siteId)
            ->select("COUNT({$alias})")
            ->getQuery()
            ->getSingleScalarResult();
    }
}