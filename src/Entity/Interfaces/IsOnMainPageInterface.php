<?php

namespace App\Entity\Interfaces;

interface IsOnMainPageInterface
{
    public function isIsOnMainPage(): ?bool;

    public function setIsOnMainPage(bool $isOnMainPage): self;
}