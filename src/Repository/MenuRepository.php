<?php

namespace App\Repository;

use App\Entity\Interfaces\NestedSetsCreateDeleteInterface;
use App\Entity\Interfaces\NestedSetsMoveItemsInterface;
use App\Entity\Interfaces\NestedSetsMoveUpDownInterface;
use App\Entity\Interfaces\NodeInterface;

use App\Repository\Traits\AliasRepositoryTrait;
use App\Repository\Traits\GetQueryBuilderRepositoryTrait;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\Menu;
use Doctrine\Persistence\ManagerRegistry;

class MenuRepository extends ServiceEntityRepository
    implements NestedSetsMoveItemsInterface,
    NestedSetsMoveUpDownInterface,
    NestedSetsCreateDeleteInterface
{

    use GetQueryBuilderRepositoryTrait;

    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Menu::class);
        $this->setAlias('m');
    }

    public function create(NodeInterface $node, ?NodeInterface $parent = null): NodeInterface
    {
        return (new NestedSetsCreateDelete($this->getEntityManager(), Menu::class))->create($node, $parent);
    }

    public function delete(NodeInterface $node, bool $isSafeDelete = false): void
    {
        (new NestedSetsCreateDelete($this->getEntityManager(), Menu::class))->delete($node, $isSafeDelete);
    }

    public function getAllQueryBuilder(?int $siteId = null): QueryBuilder
    {
        $queryBuilder = $this
            ->getQueryBuilderWithSiteId($siteId)
            ->orderBy($this->getAlias() . ".tree", "ASC")
            ->addOrderBy($this->getAlias() . ".lft", "ASC");


        return $queryBuilder;
    }

    public function getAllSubItemsQueryBuilder(
        NodeInterface $menu,
        ?QueryBuilder $queryBuilder = null,
        ?int $siteId
    ): QueryBuilder {
        $queryBuilder = $this->getAllSubItemsQueryBuilder($siteId, $queryBuilder)

            ->andWhere($this->getAlias() . ".tree=:tree")->setParameter("tree", $menu->getTree())
            ->andWhere($this->getAlias() . ".lft>:lft")->setParameter("lft", $menu->getLft())
            ->andWhere($this->getAlias() . ".rgt<:rgt")->setParameter("rgt", $menu->getRgt());

        return $queryBuilder;
    }



    public function getAllRootsQueryBuilder(): QueryBuilder
    {
        return $this
            ->getQueryBuilder()
            ->andWhere($this->getAlias() . ".lft=:lft")
            ->setParameter("lft", 1);
    }

    public function move(NodeInterface $node, ?NodeInterface $parent): void
    {
        try {
            (new NestedSetsMoveItems($this->getEntityManager(), Menu::class))->move($node, $parent);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function upDown(NodeInterface $node, bool $isUp = true): void
    {
        try {
            (new NestedSetsMoveItems($this->getEntityManager(), Menu::class))->upDown($node, $isUp);
        } catch (\Throwable $exception) {
            throw $exception;
        }
    }

    public function findOneByNameQueryBuilder(string $name): QueryBuilder
    {
        return $this
            ->getQueryBuilder()
            ->leftJoin($this->getAlias() . ".config", "config")
            ->andWhere($this->getAlias() . ".name=:name")
            ->setParameter("name", $name);
    }

    public function getParentByItemId(int $id): ?NodeInterface
    {
        $sql = "SELECT parent_id FROM `" . $this->getClassMetadata()->getTableName() . "` WHERE `id`=:id";
        $parentId = $this->getEntityManager()->getConnection()->fetchOne($sql, ["id" => $id]);
        return $this->find($parentId);
    }

    public function getParentsByItemQueryBuilder(
        NodeInterface $menu,
        ?int $deep = null,
        ?QueryBuilder $queryBuilder = null
    ): QueryBuilder {

        $alias = $this->getAlias();

        $queryBuilder = $this
            ->getQueryBuilder($queryBuilder)
            ->andWhere("{$alias}.lft<=:lft")->setParameter('lft', $menu->getLft())
            ->andWhere("{$alias}.rgt>=:rgt")->setParameter('rgt', $menu->getRgt())
            ->andWhere("{$alias}.tree=:tree")->setParameter('tree', $menu->getTree());

        if ($deep !== null) {
            $queryBuilder
                ->andWhere("{$alias}.lvl=:deep")
                ->setParameter("deep", $menu->getLvl() - $deep)
            ;
        }

        return $queryBuilder;
    }

    public function updateUrlInSubElements(NodeInterface $menu, string $oldUrl, ?int $siteId = null): void
    {
        $items = $this
            ->getAllQueryBuilder($siteId)
            ->andWhere($this->getAlias() . ".tree=:tree")
            ->setParameter("tree", $menu->getTree())
            ->getQuery()
            ->getResult();

        if (!empty($items)) {
            foreach ($items as $item) {
                $newPath = str_replace($oldUrl, $menu->getUrl(), $item->getPath());
                $item->setPath($newPath);
            }
        }
    }

    public function getAllTrees(int $siteId): ?array
    {
        return $this
            ->getAllRootsQueryBuilder()
            ->andWhere($this->getAlias() . ".siteId=:site")
            ->setParameter("site", $siteId)
            ->getQuery()
            ->getResult();
    }

    public function getAllMenuQueryBuilder(int $siteId, array $params = []): ?QueryBuilder
    {
        $queryBuilder = $this->getAllQueryBuilder($siteId);
        $alias = $this->getAlias();

        foreach ($params as $key => $value) {
            $queryBuilder
                ->andWhere("{$alias}.{$key}=:{$key}")
                ->setParameter($key, $value);
            ;
        }

        return $queryBuilder;
    }

    public function getMenuLength(int $siteId): int
    {
        $alias = $this->getAlias();

        return $this
            ->getAllMenuQueryBuilder($siteId)
            ->select("COUNT({$alias}.id)")
            ->getQuery()
            ->getSingleScalarResult()
            ;
    }
}