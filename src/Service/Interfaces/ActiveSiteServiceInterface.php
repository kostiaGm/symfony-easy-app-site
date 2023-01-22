<?php

namespace App\Service\Interfaces;

interface ActiveSiteServiceInterface
{
    public function get(): array;
    public function getId(int $default = 0): int;
    public function getDomain(): string;
    public function getRoute(bool $isParent = false): string;
    public function getFilters(): array;
}