<?php

namespace App\Controller\Traits;

use App\Entity\Site;
use App\Exceptions\SiteException;

trait ActiveTrait
{
    public function getActiveSiteId(int $default = 0): int
    {
        if (!isset($this->activeSite)) {
            throw new SiteException("Can't find the parameter activeSite in your class");
        }

        if (!($this->activeSite instanceof Site)) {
            throw new SiteException("The parameter activeSite must be ".Site::class);
        }

        return $this->activeSite->getId() ?? $default;
    }

}