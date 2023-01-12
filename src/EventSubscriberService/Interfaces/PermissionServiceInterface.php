<?php

namespace App\EventSubscriberService\Interfaces;

use App\Entity\Interfaces\PermissionInterface;

interface PermissionServiceInterface
{
    public function check(PermissionInterface $permission): bool;
}