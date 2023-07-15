<?php

namespace App\Entity\Interfaces;

interface IsDefaultInterface
{
    public function isDefault(): ?bool;

    public function setIsDefault(?bool $isDefault): self;
}