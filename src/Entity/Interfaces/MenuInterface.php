<?php

namespace App\Entity\Interfaces;


use App\Entity\Menu;
use App\Entity\Page;
use Doctrine\Common\Collections\Collection;


interface MenuInterface
{
    public function getId(): ?int;

    public function setId(?int $id): \App\Entity\Menu;

    public function getRoute(): ?string;

    public function setRoute(?string $route): \App\Entity\Menu;

    public function getUrl(): ?string;

    public function setUrl(?string $url): \App\Entity\Menu;

    public function getPath(): ?string;

    public function setPath(?string $path): \App\Entity\Menu;

    public function getType(): ?int;

    public function setType(?int $type): \App\Entity\Menu;

    public function getTransliteratedUrl(): string;

    /**
     * @return Collection<int, Page>
     */
    public function getPages(): Collection;

    public function addPage(Page $page): \App\Entity\Menu;

    public function removePage(Page $page): \App\Entity\Menu;

    public function getEntityId(): ?int;

    public function setEntityId(?int $entityId): \App\Entity\Menu;

    public function isIsTopMenu(): ?bool;

    public function setIsTopMenu(bool $isTopMenu): \App\Entity\Menu;

    public function isIsLeftMenu(): ?bool;

    public function setIsLeftMenu(bool $isLeftMenu): \App\Entity\Menu;

    public function isIsBottomMenu(): ?bool;

    public function setIsBottomMenu(bool $isBottomMenu): \App\Entity\Menu;

    public function getName(): ?string;

    public function setName(?string $name): \App\Entity\Menu;
}