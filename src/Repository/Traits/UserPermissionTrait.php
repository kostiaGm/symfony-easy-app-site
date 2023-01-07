<?php

namespace App\Repository\Traits;

use App\Entity\Role;
use Symfony\Component\Security\Core\User\UserInterface;

trait UserPermissionTrait
{
    public function getUsersIdsByMyGroup(): void
    {
        if (empty($this->activeUser) || in_array(Role::ROLE_ADMIN, $this->activeUser->getRoles())) {
            return;
        }

        $myRolesIds = [];
        foreach ($this->activeUser->getRolesCollection() as $role) {
            $myRolesIds[] = $role->getId();
        }

        if (empty($myRolesIds)) {
            return;
        }
        $sql = "SELECT `user_id` FROM `user_role` WHERE `role_id` IN (".implode(',', $myRolesIds).")";
        $result = $this->getEntityManager()->getConnection()->fetchAllNumeric($sql);

    //    $this->activeUser->setOtherUserIdsWithMyGroups($result);
    }
}