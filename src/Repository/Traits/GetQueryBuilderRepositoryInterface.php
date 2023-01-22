<?php

namespace App\Repository\Traits;

use Doctrine\ORM\QueryBuilder;

interface GetQueryBuilderRepositoryInterface
{
    public function getQueryBuilder(?QueryBuilder $queryBuilder = null, string $indexBy = null): QueryBuilder;

    public function getQueryBuilderWithSiteId(?int $siteId,?QueryBuilder $queryBuilder = null,string $indexBy = null): QueryBuilder;

    public function getDataLength(int $siteId): int;
}