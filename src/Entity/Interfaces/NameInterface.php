<?php

namespace App\Entity\Interfaces;

interface NameInterface
{
    public function getName(): ?string;

    public function setName(?string $name): self;
}
