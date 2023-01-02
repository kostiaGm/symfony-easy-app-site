<?php

namespace App\Repository\Traits;

trait AliasRepositoryTrait
{

    private string $alias = '';

    public function getAlias(): string
    {
        return $this->alias;
    }

    public function setAlias(string $alias): void
    {
        $this->alias = $alias;
    }
}