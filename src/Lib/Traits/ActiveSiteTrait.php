<?php

namespace App\Lib\Traits;

trait ActiveSiteTrait
{
    private function getActiveSiteId(int $default = 0): int
    {
        return $this->getActiveSite()['id'] ?? $default;
    }

    private function getActiveSite(): array
    {
        return $this->params->get('site')[$this->request->getCurrentRequest()->getHost()] ?? [];
    }
}