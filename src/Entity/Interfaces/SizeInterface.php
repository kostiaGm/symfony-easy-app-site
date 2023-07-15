<?php

namespace App\Entity\Interfaces;

interface SizeInterface
{
    public function getWidth(): ?int;

    public function getHeight(): ?int;

    public function setWidth(?int $width): self;

    public function setHeight(?int $height): self;
}