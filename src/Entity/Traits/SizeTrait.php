<?php

namespace App\Entity\Traits;

trait SizeTrait
{
    /** @ORM\Column(type="integer") */
    private ?int $width;

    /** @ORM\Column(type="integer") */
    private ?int $height;

    public function getWidth(): ?int
    {
        return $this->width;
    }

    public function getHeight(): ?int
    {
        return $this->height;
    }

    public function setWidth(?int $width): self
    {
        $this->width = $width;
        return $this;
    }

    public function setHeight(?int $height): self
    {
        $this->height = $height;
        return $this;
    }
}
