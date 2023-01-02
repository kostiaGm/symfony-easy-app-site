<?php

namespace App\Entity\Interfaces;

use App\Entity\Site;

interface JoinSiteInterface
{
    public function getSite(): ?Site;
    public function setSite(?Site $site): self;
}