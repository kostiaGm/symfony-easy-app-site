<?php

namespace App\Entity\Traits;

trait PermissionTrait
{
    /**
     * @ORM\ManyToOne(targetEntity=User::class)
     */
    private $editor;

    /**
     * @ORM\Column(type="smallint")
     */
    private $permissionMode;

    public function getPermissionMode(): ?int
    {
        return $this->permissionMode;
    }

    public function setPermissionMode(int $permissionMode): self
    {
        $this->permissionMode = $permissionMode;

        return $this;
    }
}