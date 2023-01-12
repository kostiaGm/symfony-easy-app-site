<?php

namespace App\Entity\Interfaces;

interface PermissionInterface
{
    public const EVERY_ONE_READING_ALLOWED = 1;
    public const GROUPS_READING_ALLOWED = 2;
    public const AUTHOR_ONLY_READING_ALLOWED = 3;
    public const GROUPS_EDIT_ALLOWED = 4;

    public const TYPES = [
        self::EVERY_ONE_READING_ALLOWED => 'Allow everyone to read',
        self::GROUPS_READING_ALLOWED => 'Allow groups only to read',
        self::AUTHOR_ONLY_READING_ALLOWED => 'Allow author only to read',
        self::GROUPS_EDIT_ALLOWED => 'Allow groups to read and edit',
    ];

    public function getPermissionMode(): ?int;

    public function setPermissionMode(int $permissionMode): self;
}