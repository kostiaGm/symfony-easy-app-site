<?php

namespace App\Entity\Traits;

use App\Entity\Menu;

trait IsJoinMenuTrait
{


    public function getMenu(): ?Menu
    {
        return $this->menu;
    }

    public function setMenu(?Menu $menu): self
    {
        $this->menu = $menu;

        return $this;
    }
}