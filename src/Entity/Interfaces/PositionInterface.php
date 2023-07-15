<?php

namespace App\Entity\Interfaces;

interface PositionInterface
{
    public function getPosition(): ?int;

    public function setPosition(?int $position): self;
}
