<?php

namespace App\Entity\Traits;

trait IsDefaultTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    private ?bool $isDefault = null;

    public function isDefault(): ?bool
    {
        return $this->isDefault;
    }

    public function setIsDefault(?bool $isDefault): self
    {
        $this->isDefault = $isDefault;
        return $this;
    }
}