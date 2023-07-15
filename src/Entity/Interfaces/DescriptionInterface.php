<?php

namespace App\Entity\Interfaces;

interface DescriptionInterface
{
    public function getDescription(): ?string;

    public function setDescription(?string $name): self;
}
