<?php

namespace App\Entity\Traits;

trait SiteTrait
{
    /**
     * @ORM\Column(type="integer")
     */
    private $siteId;

    public function getSiteId(): ?int
    {
        return $this->siteId;
    }

    public function setSiteId(int $siteId): self
    {
        $this->siteId = $siteId;

        return $this;
    }
}