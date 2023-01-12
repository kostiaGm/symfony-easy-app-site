<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\AuthorInterface;
use App\EventSubscriberService\Interfaces\DBSAuthorInterface;
use App\EventSubscriberService\Interfaces\DBSInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class DBSAuthor implements DBSAuthorInterface, DBSInterface
{
    private TokenStorageInterface $tokenStorage;

    public function __construct(TokenStorageInterface $tokenStorage)
    {
        $this->tokenStorage = $tokenStorage;
    }

    public function create($entity): void
    {
        if ($entity instanceof AuthorInterface) {
            $entity->seAuthor($this->tokenStorage->getToken()->getUser());
        }
    }

    public function update($entity): void
    {
       $this->create($entity);
    }
}