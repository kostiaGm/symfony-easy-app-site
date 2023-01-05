<?php

namespace App\EventSubscriberService;

use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Interfaces\MenuInterface;
use App\Entity\Menu;
use App\EventSubscriberService\Interfaces\DBSInterface;
use App\EventSubscriberService\Interfaces\DBSMenuInterface;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Repository\MenuRepository;

class DBSMenu implements DBSMenuInterface, DBSInterface
{
    private ActiveSiteServiceInterface $activeSiteService;
    private MenuRepository $menuRepository;

    public function __construct(
        ActiveSiteServiceInterface $activeSiteService,
        MenuRepository $menuRepository
    ) {
        $this->activeSiteService = $activeSiteService;
        $this->menuRepository = $menuRepository;
    }

    public function create($entity): void
    {
        if (!$entity instanceof IsJoinMenuInterface) {
            return;
        }

        $menu = new Menu();
        $menu->setStatus(Menu::STATUS_ACTIVE);
        $menu->setName($entity->getName());
        $menu->setRoute($entity->getRenderPageRoute());
        $path = '';

        if ($entity->getMenu() !== null) {
            $menu->setUrl($menu->getTransliteratedUrl());
            $path = $this->getMenuPath($entity->getMenu()) . '/' . $menu->getUrl();
            $path = preg_replace(['/\/{2,}/', '/^\/{1,}/'], ['/', ''], $path);
        }

        $menu->setPath($path);
        $menu->setType(Menu::SITE_PAGE_TYPE);
        $this->menuRepository->create($menu, $entity->getMenu());
        $entity->setMenu($menu);
    }

    public function getMenuPath(MenuInterface $menu): string
    {
        $result = '';
        $parentMenuItems = $this
            ->menuRepository
            ->getParentsByItemQueryBuilder($menu)
            ->getQuery()
            ->getArrayResult();
        foreach ($parentMenuItems as $item) {
            $result .=  $item['url'];
        }

        return $result;
    }

    public function update($entity): void
    {
        // TODO: Implement update() method.
    }

}