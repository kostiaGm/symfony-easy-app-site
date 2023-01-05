<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\OwnerInterface;
use App\EventSubscriberService\Interfaces\DBSInterface;
use App\EventSubscriberService\Interfaces\DBSOwnerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DBSOwner implements DBSOwnerInterface, DBSInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function create($entity): void
    {
        if ($entity instanceof OwnerInterface && empty($entity->getOwner())) {
            $entity->setOwner($this->tokenStorage->getToken()->getUser());
        }
    }

    public function update($entity): void
    {
       $this->create($entity);
    }
}