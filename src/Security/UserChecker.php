<?php

namespace App\Security;

use App\Entity\User;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    private ActiveSiteServiceInterface $activeSiteService;
    public function __construct(ActiveSiteServiceInterface $activeSiteService)
    {
        $this->activeSiteService = $activeSiteService;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User ) {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user || $user->getSiteId() != $this->activeSiteService->getId()) {
            throw new CustomUserMessageAccountStatusException('Your user account not found.');
        }
    }
}