<?php

namespace App\Controller\Traits;

trait ActiveTrait
{
    public function getActiveSiteId(string $domain, int $default = 0): int
    {
        return $this->getParameter('site')[$domain]['id'] ?? $default;
    }
}