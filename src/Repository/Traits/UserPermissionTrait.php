<?php

namespace App\Repository\Traits;

use App\Entity\Interfaces\PermissionInterface;
use App\Entity\Role;
use Doctrine\ORM\QueryBuilder;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserPermissionTrait
{
    public function getUsersIdsByMyGroup(QueryBuilder $queryBuilder, ?UserInterface $user): void
    {
        if (empty($user) || in_array(Role::ROLE_ADMIN, $user->getRoles())) {
            return;
        }

        $myRolesIds = [];
        foreach ($user->getRolesCollection() as $role) {
            $myRolesIds[] = $role->getId();
        }

        if (empty($myRolesIds)) {
            return;
        }

        $sql = "SELECT user_id FROM user_role WHERE role_id 
                IN (".implode(',', $myRolesIds).") AND user_id != {$user->getId()}";
        $ids =  $this->getEntityManager()->getConnection()->fetchAllNumeric($sql);

        $alias = $this->getAlias();
        $queryBuilder
            ->andWhere("{$alias}.permissionMode=:everyoneReadingAllowed 
            OR ({$alias}.permissionMode=:groupOnlyReadingAllowed AND {$alias}.author IN (:users))")
            ->setParameter('everyoneReadingAllowed', PermissionInterface::EVERY_ONE_READING_ALLOWED)
            ->setParameter('groupOnlyReadingAllowed', PermissionInterface::GROUPS_READING_ALLOWED)
            ->setParameter("users", $ids)

        ;

    }
}