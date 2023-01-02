<?php

namespace App\Entity\Interfaces;

interface SafeDeleteInterface
{
    public const STATUS_DELETED = 3;

    public function setStatusDelete(): void;
    public function getStatusDelete(): bool;

    public function getDeletedAt(): ?\DateTimeInterface;
    public function setDeletedAt(?\DateTimeInterface $deletedAt): self;
}