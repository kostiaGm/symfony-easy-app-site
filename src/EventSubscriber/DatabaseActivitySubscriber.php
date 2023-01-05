<?php

namespace App\EventSubscriber;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\SiteInterface;
use App\EventSubscriberService\Interfaces\DBSActiveSiteInterface;
use App\EventSubscriberService\Interfaces\DBSChangeDateDateInterface;
use App\EventSubscriberService\Interfaces\DBSMenuInterface;
use App\EventSubscriberService\Interfaces\DBSOwnerInterface;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Traits\ActiveSiteTrait;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;


class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    private DBSOwnerInterface $dBSOwner;
    private DBSMenuInterface $dBSMenu;
    private DBSChangeDateDateInterface $dBSChangeDateDate;
    private DBSActiveSiteInterface $dBSActiveSite;

    public function __construct(
        DBSOwnerInterface $dBSOwner,
        DBSMenuInterface $dBSMenu,
        DBSChangeDateDateInterface $dBSChangeDateDate,
        DBSActiveSiteInterface $dBSActiveSite
    ) {
        $this->dBSOwner = $dBSOwner;
        $this->dBSMenu = $dBSMenu;
        $this->dBSChangeDateDate = $dBSChangeDateDate;
        $this->dBSActiveSite = $dBSActiveSite;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->dBSOwner->create($entity);
        $this->dBSActiveSite->create($entity);

        if ($entity instanceof ChangeDataDayInterface) {
            $entity->setCreatedAt(new \DateTime('now'));
        }

        $this->dBSMenu->create($entity);
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();
        $this->dBSOwner->update($entity);
        $this->dBSActiveSite->update($entity);
    }
}
