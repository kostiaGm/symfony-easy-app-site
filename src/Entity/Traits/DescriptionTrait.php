<?php

namespace App\Entity\Traits;

trait DescriptionTrait
{
    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }
}
