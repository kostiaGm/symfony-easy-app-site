<?php

namespace App\Repository;

use App\Entity\Role;
use App\Entity\User;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NoResultException;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Security\User\UserLoaderInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\PasswordUpgraderInterface;
use Symfony\Component\Security\Core\User\UserInterface;

/**
 * @extends ServiceEntityRepository<User>
 *
 * @method User|null find($id, $lockMode = null, $lockVersion = null)
 * @method User|null findOneBy(array $criteria, array $orderBy = null)
 * @method User[]    findAll()
 * @method User[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class UserRepository extends ServiceEntityRepository implements UserLoaderInterface
{
    use GetQueryBuilderRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, User::class);
        $this->setAlias('u');
    }

    public function loadUserByUsername($username): UserInterface
    {
        $alias = $this->getAlias();

        $q = $this
            ->getQueryBuilder()
            ->where("{$alias}.username = :username OR {$alias}.email = :email")
            ->setParameter('username', $username)
            ->setParameter('email', $username)
            ->leftJoin("{$alias}.roles", 'r')
            ->addSelect('r')
            ->getQuery();

        try {
            $user = $q->getSingleResult();
        } catch (NoResultException $e) {
            $message = sprintf(
                'Unable to find an active admin AcmeUserBundle:User object identified by "%s".',
                $username
            );
            throw new UsernameNotFoundException($message, 1, $e);
        }
        return $user;
    }


    public function add(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(User $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * Used to upgrade (rehash) the user's password automatically over time.
     */
    public function upgradePassword(PasswordAuthenticatedUserInterface $user, string $newHashedPassword): void
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', \get_class($user)));
        }

        $user->setPassword($newHashedPassword);

        $this->add($user, true);
    }

    public function getUsersIdsByMyGroup(User $user): void
    {
        $myRolesIds = [];
        foreach ($user->getRolesCollection() as $role) {
            $myRolesIds[] = $role->getId();
        }

        if (empty($myRolesIds)) {
            return;
        }
        $sql = "SELECT `user_id` FROM `user_role` WHERE `role_id` IN (".implode(',', $myRolesIds).")";
        $result = $this->getEntityManager()->getConnection()->fetchAllNumeric($sql);

        $user->setOtherUserIdsWithMyGroups($result);
    }
}
