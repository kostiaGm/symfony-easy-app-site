<?php

namespace App\Entity\Interfaces;

interface StatusInterface
{
    public const ACTIVE = 1;
    public const INACTIVE = 2;
    public const DELETED = 3;

    public const STATUSES = [
        self::ACTIVE => 'Active',
        self::INACTIVE => 'Inactive',
        self::DELETED => 'Deleted',
    ];

    public function getStatus(): ?int;
    public function setStatus(int $status): self;
}
