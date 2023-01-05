<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\SiteInterface;
use App\EventSubscriberService\Interfaces\DBSActiveSiteInterface;
use App\EventSubscriberService\Interfaces\DBSInterface;
use App\Service\Interfaces\ActiveSiteServiceInterface;

class DBSActiveSite implements DBSInterface, DBSActiveSiteInterface
{
    private ActiveSiteServiceInterface $activeSiteService;

    public function __construct(ActiveSiteServiceInterface $activeSiteService)
    {
        $this->activeSiteService = $activeSiteService;
    }

    public function create($entity): void
    {
        if (!$entity instanceof SiteInterface) {
            return;
        }

        $entity->setSiteId($this->activeSiteService->getId());
    }

    public function update($entity): void
    {
        $this->create($entity);
    }
}
