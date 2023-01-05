<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\SafeDeleteInterface;
use App\EventSubscriberService\Interfaces\DBSChangeDateDateInterface;
use App\EventSubscriberService\Interfaces\DBSInterface;

class DBSChangeDateDate implements DBSChangeDateDateInterface, DBSInterface
{
    public function create($entity): void
    {
        if ($entity instanceof ChangeDataDayInterface) {
            $entity->setCreatedAt(new \DateTime('now'));
        }
    }

    public function update($entity): void
    {
        if ($entity instanceof ChangeDataDayInterface) {
            $dateNow = new \DateTime('now');

            if ($entity instanceof SafeDeleteInterface && $entity->getStatusDelete()) {
                $entity->getDeletedAt($dateNow);
            } else {
                $entity->setUpdatedAt($dateNow);
            }
        }
    }

}