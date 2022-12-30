<?php

namespace App\Repository;

use App\Entity\Site;
use App\Exceptions\SiteException;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Site>
 *
 * @method Site|null find($id, $lockMode = null, $lockVersion = null)
 * @method Site|null findOneBy(array $criteria, array $orderBy = null)
 * @method Site[]    findAll()
 * @method Site[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SiteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Site::class);
    }

    public function add(Site $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Site $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @param string $domain
     * @param int|null $status
     * @return Site
     * @throws SiteException
     * @throws \Doctrine\ORM\NonUniqueResultException
     */
    public function getSiteByDomain(string $domain, ?int $status): Site
    {
        $queryBuilder = $this->createQueryBuilder('s')
            ->where("s.domain=:domain")
            ->setParameter("domain", $domain);

        if ($status !== null) {
            $queryBuilder
                ->andWhere("s.status=:status")
                ->setParameter("status", $status);
        }

        $result = $queryBuilder
            ->getQuery()
            ->getOneOrNullResult();

        if (empty($result)) {
            throw new SiteException("Site [ {$domain} ] not found");
        }

        return $result;
    }

    public function getCountByStatus(?int $status): int
    {
        $queryBuilder = $this
            ->createQueryBuilder('s')
            ->select("COUNT(s.id)");

        if ($status !== null) {
            $queryBuilder
                ->where("s.status=:status")
                ->setParameter("status", $status);
        }

        return (int)
            $queryBuilder
                ->getQuery()
                ->getSingleScalarResult();
    }
}

