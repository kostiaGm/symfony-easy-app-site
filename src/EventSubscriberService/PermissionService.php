<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\PermissionInterface;
use App\EventSubscriberService\Interfaces\PermissionServiceInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class PermissionService implements PermissionServiceInterface
{
    private $activeController;
    private $activeAction;

    private UserInterface $user;

    public function __construct(RequestStack $requestStack, TokenStorageInterface $tokenStorage)
    {
        if ($tokenStorage->getToken() !== null) {
            $this->user = $tokenStorage->getToken()->getUser();
        }
    }

    public function check(PermissionInterface $permission): bool
    {
        return false;
    }
}
