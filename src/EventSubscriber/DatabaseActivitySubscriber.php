<?php

namespace App\EventSubscriber;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\EventSubscriberService\Interfaces\DBSActiveSiteInterface;
use App\EventSubscriberService\Interfaces\DBSAuthorInterface;
use App\EventSubscriberService\Interfaces\DBSChangeDateDateInterface;
use App\EventSubscriberService\Interfaces\DBSMenuInterface;
use App\EventSubscriberService\Interfaces\DBSOwnerInterface;
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
    private DBSAuthorInterface $dBSAuthor;

    public function __construct(
        DBSOwnerInterface $dBSOwner,
        DBSMenuInterface $dBSMenu,
        DBSChangeDateDateInterface $dBSChangeDateDate,
        DBSActiveSiteInterface $dBSActiveSite,
        DBSAuthorInterface $dBSAuthor
    ) {
        $this->dBSOwner = $dBSOwner;
        $this->dBSMenu = $dBSMenu;
        $this->dBSChangeDateDate = $dBSChangeDateDate;
        $this->dBSActiveSite = $dBSActiveSite;
        $this->dBSAuthor = $dBSAuthor;
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
        $this->dBSAuthor->create($entity);

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
        $this->dBSAuthor->update($entity);
    }
}
