<?php

namespace App\Repository;

use App\Entity\Interfaces\NodeInterface;
use App\Exceptions\NestedSetsException;
use Doctrine\ORM\EntityManagerInterface;

abstract class NestedSetsBaseAbstract
{
    private EntityManagerInterface $entityManager;
    private string $tableName;
    private string $entityClassName;

    protected $alias = 'ns';

    public function __construct(EntityManagerInterface $entityManager, ?string $entityClassName = null)
    {
        $this->entityManager = $entityManager;
        if (!empty($entityClassName)) {
            $this->setEntityClassName($entityClassName);
        }
    }

    public function setEntityClassName(string $entityClassName): void
    {
        if (!is_subclass_of(new $entityClassName(), NodeInterface::class)) {
            throw new NestedSetsException(
                sprintf(
                    "The class %s not implement interface %s",
                    $entityClassName,
                    NodeInterface::class
                )
            );
        }
        $this->entityClassName = $entityClassName;
        $this->tableName = $this->getEntityManager()->getClassMetadata($entityClassName)->getTableName();
    }

    protected function getEntity(int $id, int $lft, int $rgt, int $lvl, int $parentId, int $tree): ?NodeInterface
    {
        $node = new $this->entityClassName();
        $node
            ->setId($id)
            ->setLft($lft)
            ->setRgt($rgt)
            ->setLvl($lvl)
            ->setParentId($parentId)
            ->setTree($tree);
        return $node;
    }

    protected function getTableName(): string
    {
        return $this->tableName;
    }

    protected function getEntityManager(): EntityManagerInterface
    {
        return $this->entityManager;
    }
}