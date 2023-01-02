<?php

namespace App\EventSubscriber;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Interfaces\JoinSiteInterface;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\SafeDeleteInterface;
use App\Entity\Menu;
use App\Entity\Site;
use App\Repository\MenuRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Twig\Environment;

class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    private Environment $twig;
    private MenuRepository $menuRepository;

    public function __construct(Environment $twig,MenuRepository $menuRepository)
    {
        $this->twig = $twig;
        $this->menuRepository = $menuRepository;
    }

    public function getSubscribedEvents()
    {
        return [
            Events::prePersist,
            Events::preUpdate,
        ];
    }

    public function prePersist(PrePersistEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof JoinSiteInterface) {
            $this->saveJoinActiveSite($entity);
        }

        if ($entity instanceof ChangeDataDayInterface) {
            $entity->setCreatedAt(new \DateTime('now'));
        }

        if ($entity instanceof IsJoinMenuInterface) {

            $menu = new Menu();
            $menu->setStatus(Menu::STATUS_ACTIVE);
            $menu->setName($entity->getName());
            $menu->setRoute($entity->getRenderPageRoute());
            $path = '';
            dump($entity->getMenu());
            if ($entity->getMenu() !== null) {
                $menu->setUrl($menu->getTransliteratedUrl());
                $path = $this->getMenuPath($entity->getMenu()) . '/' . $menu->getUrl();
                dump($path);
                $path = preg_replace(['/\/{2,}/', '/^\/{1,}/'], ['/', ''], $path);
            } else {
                $menu->setUrl('');
            }
            $menu->setPath($path);
            $menu->setType(Menu::SITE_PAGE_TYPE);
            $this->menuRepository->create($menu, $entity->getMenu());
            $entity->setMenu($menu);
        }
    }


    public function getMenuPath(NodeInterface $menu): string
    {
        $result = '';
        $parentMenuItems = $this
            ->menuRepository
            ->getParentsByItemQueryBuilder($menu)
            ->getQuery()
            ->getArrayResult();

        dump($parentMenuItems);
        foreach ($parentMenuItems as $item) {
            $result .=  $item['url'];
        }
        dump( $result);
        return $result;
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof ChangeDataDayInterface) {
            $dateNow = new \DateTime('now');

            if ($entity instanceof SafeDeleteInterface && $entity->getStatusDelete()) {
                $entity->getDeletedAt($dateNow);
            } else {
                $entity->setUpdatedAt($dateNow);
            }
        }
    }

    private function saveJoinActiveSite(JoinSiteInterface $entity): void
    {
        $activeSite = $this->twig->getGlobals()['activeSite'] ?? null;
        if ($activeSite instanceof Site) {
            $entity->setSite($activeSite);
        }
    }
}