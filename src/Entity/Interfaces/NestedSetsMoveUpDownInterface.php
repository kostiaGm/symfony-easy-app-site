<?php

namespace App\Entity\Interfaces;

interface NestedSetsMoveUpDownInterface
{
    public function upDown(NodeInterface $node, bool $isUp = true): void;
}