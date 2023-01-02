<?php

namespace App\Entity\Traits;

trait IsOnMainPageTrait
{
    /**
     * @ORM\Column(type="boolean")
     */
    private bool $isOnMainPage = false;

    public function isIsOnMainPage(): ?bool
    {
        return $this->isOnMainPage;
    }

    public function setIsOnMainPage(bool $isOnMainPage): self
    {
        $this->isOnMainPage = $isOnMainPage;

        return $this;
    }
}