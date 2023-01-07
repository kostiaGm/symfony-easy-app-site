<?php

namespace App\Repository;

use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Menu;
use App\Entity\Page;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use App\Repository\Traits\UserPermissionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @extends ServiceEntityRepository<Page>
 *
 * @method Page|null find($id, $lockMode = null, $lockVersion = null)
 * @method Page|null findOneBy(array $criteria, array $orderBy = null)
 * @method Page[]    findAll()
 * @method Page[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PageRepository extends ServiceEntityRepository
{
    use GetQueryBuilderRepositoryTrait, UserPermissionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Page::class);
        $this->setAlias('p');
    }

    public function add(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Page $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getAllQueryBuilder(int $siteId): QueryBuilder
    {
        $alias = $this->getAlias();
        $queryBuilder = $this
            ->getQueryBuilderWithSiteId($siteId)
            ->leftJoin("{$alias}.menu", 'm')
            ->addSelect('m')
            ;

        $this->getUsersIdsByMyGroup();

        dump($this->getActiveUser);

        return $queryBuilder;
    }

    public function getBySlug(int $siteId, string $slug): IsJoinMenuInterface
    {
        $queryBuilder = $this
            ->getAllQueryBuilder($siteId)
            ->andWhere('m.path=:slug')
            ->setParameter('slug', $slug)
        ;

        $result = $queryBuilder->getQuery()->getOneOrNullResult();

        if (empty($result)) {
            throw new NotFoundHttpException("Page [ $slug ] not found");
        }

        return $result;
    }

    public function getPreviewOnMain(int $siteId, int $limit): ?array
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilder()
            ->andWhere("{$alias}.siteId=:siteId")->setParameter("siteId", $siteId)
            ->andWhere("{$alias}.isOnMainPage=:isOnMainPage")->setParameter("isOnMainPage", true)
            ->addOrderBy("{$alias}.id", "DESC")
            ->setMaxResults($limit)
            ->getQuery()
            ->getResult()
        ;
    }
}

