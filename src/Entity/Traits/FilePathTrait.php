<?php

namespace App\Entity\Traits;

trait FilePathTrait
{
    /**
     *  @ORM\Column(type="string", length=255, nullable=true)
     */
    private ?string $path;

    public function getPath(): ?string
    {
        return $this->path;
    }

    public function setPath(?string $path): self
    {
        $this->path = $path;
        return $this;
    }
}