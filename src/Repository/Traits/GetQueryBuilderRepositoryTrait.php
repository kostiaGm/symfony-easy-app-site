<?php

namespace App\Repository\Traits;

use App\Entity\Interfaces\SafeDeleteInterface;
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

    public function getDataLengthQueryBuilder(int $siteId): QueryBuilder
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilderWithSiteId($siteId)
            ->select("COUNT({$alias})");
    }

    public function getDataLength(int $siteId): int
    {
        return $this
            ->getDataLengthQueryBuilder($siteId)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getDataLengthInBin(int $siteId, int $status = SafeDeleteInterface::STATUS_DELETED): int
    {
        $alias = $this->getAlias();
        return $this
            ->getDataLengthQueryBuilder($siteId)
            ->andWhere("{$alias}.status=:status")
            ->setParameter("status", $status)
            ->getQuery()
            ->getSingleScalarResult();
    }

    public function getByIdArray(array $ids): QueryBuilder
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilder()
            ->andWhere("{$alias}.id IN (:id)")
            ->setParameter('id', $ids)
            ;
    }
}