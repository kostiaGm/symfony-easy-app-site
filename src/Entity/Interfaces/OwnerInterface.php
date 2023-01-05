<?php

namespace App\Entity\Interfaces;

use App\Entity\User;

interface OwnerInterface
{
    public function getOwner(): ?User;

    public function setOwner(?User $owner): self;
}