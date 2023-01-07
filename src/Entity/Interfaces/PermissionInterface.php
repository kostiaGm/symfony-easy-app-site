<?php

namespace App\Entity\Interfaces;

interface PermissionInterface
{
    public function getPermissionMode(): ?int;

    public function setPermissionMode(int $permissionMode): self;
}