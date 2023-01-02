<?php

namespace App\Entity\Interfaces;

interface ImageInterface
{
    public function getImage(): ?string;

    public function setImage(?string $image): self;
}