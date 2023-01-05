<?php

namespace App\Service\Interfaces;

interface ActiveSiteServiceInterface
{
    public function get(): array;
    public function getId(int $default = 0): int;
}