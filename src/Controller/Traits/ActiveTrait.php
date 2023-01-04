<?php

namespace App\Controller\Traits;

trait ActiveTrait
{
    protected function getActiveSiteId(string $domain, int $default = 0): int
    {
        return $this->getActiveSite($domain)['id'] ?? $default;

    }

    protected function getActiveSite(string $domain): array
    {
        return $this->getParameter('site')[$domain] ?? [];
    }
}