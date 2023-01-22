<?php

namespace App\Repository;

use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Menu;
use App\Entity\Page;
use App\Entity\PageFilter;
use App\Repository\Interfaces\UserPermissionInterface;
use App\Repository\Traits\FilterRepositoryInterface;
use App\Repository\Traits\GetQueryBuilderRepositoryInterface;
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
class PageRepository extends ServiceEntityRepository implements GetQueryBuilderRepositoryInterface
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

    public function getAllQueryBuilder(int $siteId, int $status = Page::STATUS_ACTIVE): QueryBuilder
    {
        $alias = $this->getAlias();
        $queryBuilder = $this
            ->getQueryBuilderWithSiteId($siteId)
            ->andWhere("{$alias}.status=:status")
            ->setParameter("status", $status)
            ->leftJoin("{$alias}.menu", 'm')
            ->addSelect('m')
            ;

        return $queryBuilder;
    }

    public function getBySlugQueryBuilder(int $siteId, string $slug): QueryBuilder
    {
        return $this
            ->getAllQueryBuilder($siteId)
            ->andWhere('m.path=:slug')
            ->setParameter('slug', $slug)
        ;
    }

    public function getByIdQueryBuilder(int $id): QueryBuilder
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilder()
            ->andWhere("{$alias}.id=:id")
            ->setParameter('id', $id)
            ;
    }

    public function getPreviewOnMainQueryBuilder(int $siteId, int $limit): QueryBuilder
    {
        $alias = $this->getAlias();
        return $this
            ->getQueryBuilder()
            ->innerJoin("{$alias}.menu", "m")
            ->addSelect("m")
            ->andWhere("{$alias}.siteId=:siteId")->setParameter("siteId", $siteId)
            ->andWhere("{$alias}.isOnMainPage=:isOnMainPage")->setParameter("isOnMainPage", true)
            ->setMaxResults($limit)
            ->addOrderBy("{$alias}.id", "DESC")
        ;
    }
}
