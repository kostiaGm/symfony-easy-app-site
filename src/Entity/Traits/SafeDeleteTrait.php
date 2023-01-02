<?php

namespace App\Entity\Traits;

use App\Entity\Interfaces\StatusInterface;

trait SafeDeleteTrait
{
    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    private ?\DateTimeInterface $deletedAt;

    public function getDeletedAt(): ?\DateTimeInterface
    {
        return $this->deletedAt;
    }

    public function setDeletedAt(?\DateTimeInterface $deletedAt): self
    {
        $this->deletedAt = $deletedAt;
        return $this;
    }

    public function setStatusDelete(): void
    {
        if ($this instanceof StatusInterface) {
            $this->setStatus(self::DELETED);
        }
    }

    public function getStatusDelete(): bool
    {
        if ($this instanceof StatusInterface) {
            return $this->getStatus() == self::DELETED;
        }
        return false;
    }
}
