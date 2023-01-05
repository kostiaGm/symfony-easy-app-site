<?php

namespace App\Security;

use App\Entity\User;
use App\Lib\Traits\ActiveSiteTrait;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Exception\AccountExpiredException;
use Symfony\Component\Security\Core\Exception\CustomUserMessageAccountStatusException;
use Symfony\Component\Security\Core\User\UserCheckerInterface;
use Symfony\Component\Security\Core\User\UserInterface;

class UserChecker implements UserCheckerInterface
{
    use ActiveSiteTrait;

    private ParameterBagInterface $params;
    private RequestStack $request;

    public function __construct(ParameterBagInterface $params, RequestStack $request)
    {
        $this->params = $params;
        $this->request = $request;
    }

    public function checkPreAuth(UserInterface $user): void
    {
        if (!$user instanceof User ) {
            return;
        }
    }

    public function checkPostAuth(UserInterface $user): void
    {
        if (!$user || $user->getSiteId() != $this->getActiveSiteId()) {
            throw new CustomUserMessageAccountStatusException('Your user account not found.');
        }
    }
}