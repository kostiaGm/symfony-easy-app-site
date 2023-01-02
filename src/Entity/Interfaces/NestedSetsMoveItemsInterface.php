<?php

namespace App\Entity\Interfaces;

interface NestedSetsMoveItemsInterface
{
    public function move(NodeInterface $node, ?NodeInterface $parent): void;
}