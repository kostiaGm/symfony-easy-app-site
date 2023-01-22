<?php

namespace App\Lib\Interfaces;

interface FilterEntityInterface
{
    public const ITEM_ANY = 'Any';

    public function getFields(): array;

}