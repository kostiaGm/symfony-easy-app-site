<?php

namespace App\Entity\Interfaces;

interface StatusInterface
{
    public const STATUS_ACTIVE = 1;
    public const STATUS_INACTIVE = 2;

    public function getStatus(): ?int;
    public function setStatus(int $status): self;
    public static function getStatuses(): array;
}
