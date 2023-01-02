<?php

namespace App\Entity\Interfaces;

use App\Entity\Menu;

interface IsJoinMenuInterface
{
    public function getMenu(): ?Menu;
    public function setMenu(?Menu $menu): self;
    public function getRenderPageRoute(): string;
}