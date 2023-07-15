<?php

namespace App\Entity\Traits;

trait NameTrait
{
    /**
     * @ORM\Column(type="string", length=65)
     */
    private ?string $name = null;

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): self
    {
        $this->name = $name;
        return $this;
    }
}
