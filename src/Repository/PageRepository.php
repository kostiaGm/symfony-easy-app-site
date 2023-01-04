<?php

namespace App\Repository;

use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Menu;
use App\Entity\Page;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
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
    use GetQueryBuilderRepositoryTrait/*,GetEntityBySugTrait*/;

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

    public function getBySlug(int $siteId, string $slug): IsJoinMenuInterface
    {
        $queryBuilder = $this
            ->getQueryBuilder()
            ->innerJoin($this->getAlias().'.menu', 'm')
            ->andWhere('m.path=:slug')
            ->andWhere('m.siteId=:m_site')
            ->setParameter('slug', $slug)
            ->setParameter('m_site', $siteId)
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
