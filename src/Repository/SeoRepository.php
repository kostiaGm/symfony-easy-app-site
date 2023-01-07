<?php

namespace App\Repository;

use App\Entity\Seo;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Seo>
 *
 * @method Seo|null find($id, $lockMode = null, $lockVersion = null)
 * @method Seo|null findOneBy(array $criteria, array $orderBy = null)
 * @method Seo[]    findAll()
 * @method Seo[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SeoRepository extends ServiceEntityRepository
{
    use GetQueryBuilderRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Seo::class);
        $this->setAlias('s');
    }

    public function add(Seo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Seo $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getByEntityQueryBuilder(string $entity, int $siteId, int $id): ?QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()
            ->leftJoin($this->getAlias().".items", "items")->addSelect('items')
            ->andWhere($this->getAlias().".siteId=:siteId")->setParameter("siteId", $siteId)
            ->andWhere($this->getAlias().".entityId=:id")->setParameter("id", $id)
            ->andWhere($this->getAlias().".entity=:entity")->setParameter("entity", $entity)
        ;

        return $queryBuilder;
    }

    public function deleteSubItems(?Seo $seo): void
    {
        if (!$seo) {
            return;
        }
        $sql = "DELETE FROM seo_item WHERE seo_id=:seoId ";
        $this->getEntityManager()->getConnection()->executeQuery($sql, ['seoId' => $seo->getId()])->rowCount();
    }

    public function getAllQueryBuilder(int $siteId): QueryBuilder
    {
        $alias = $this->getAlias();

        return $this
            ->getQueryBuilder()
            ->andWhere("{$alias}.siteId=:siteId")
            ->setParameter("siteId", $siteId);
    }
}
