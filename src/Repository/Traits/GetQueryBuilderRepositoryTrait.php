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
}