<?php

namespace App\Repository;

use App\Entity\Gallery;
use App\Entity\GallerySetting;
use App\Entity\Image;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use App\Repository\Traits\UserPermissionTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<GallerySetting>
 *
 * @method GallerySetting|null find($id, $lockMode = null, $lockVersion = null)
 * @method GallerySetting|null findOneBy(array $criteria, array $orderBy = null)
 * @method GallerySetting[]    findAll()
 * @method GallerySetting[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class GallerySettingRepository extends ServiceEntityRepository
{
    use GetQueryBuilderRepositoryTrait, UserPermissionTrait;

    public function __construct(ManagerRegistry $registry)
    {
        $this->setAlias('gs');
        parent::__construct($registry, GallerySetting::class);
    }

    public function add(GallerySetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(GallerySetting $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function getImagesQBByGallery(Gallery $gallery, bool $isInitImage = true): QueryBuilder
    {
        $queryBuilder = $this->getQueryBuilder()
        ;

        if ($gallery->getId() === null) {
            $queryBuilder->expr()->isNull("{$this->getAlias()}.gallery");
        } else {
            $queryBuilder->andWhere("{$this->getAlias()}.gallery=:galleryId")
                ->setParameter("galleryId", $gallery->getId());
        }

        if ($isInitImage) {
            foreach ($queryBuilder->getQuery()->getResult() as $settingItem) {
                $image = new Image();
                $image->setWidth($settingItem->getWidth());
                $image->setHeight($settingItem->getHeight());
                $gallery->addImage($image);
            }

        }

        return $queryBuilder;
    }
}
