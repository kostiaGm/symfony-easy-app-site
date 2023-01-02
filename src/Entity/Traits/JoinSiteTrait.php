<?php

namespace App\Entity\Traits;

use App\Entity\Site;

trait JoinSiteTrait
{
    public function getSite(): ?Site
    {
        return $this->site;
    }

    public function setSite(?Site $site): self
    {
        $this->site = $site;

        return $this;
    }
}