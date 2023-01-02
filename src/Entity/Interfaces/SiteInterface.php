<?php

namespace App\Entity\Interfaces;

interface SiteInterface
{
    public function getSiteId(): ?int;

    public function setSiteId(int $siteId): self;
}