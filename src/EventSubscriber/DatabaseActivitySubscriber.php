<?php

namespace App\EventSubscriber;

use App\Entity\Interfaces\ChangeDataDayInterface;
use App\Entity\Interfaces\IsJoinMenuInterface;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\OwnerInterface;
use App\Entity\Interfaces\SafeDeleteInterface;
use App\Entity\Interfaces\SiteInterface;
use App\Entity\Menu;
use App\Lib\Traits\ActiveSiteTrait;
use App\Repository\MenuRepository;
use Doctrine\Bundle\DoctrineBundle\EventSubscriber\EventSubscriberInterface;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PrePersistEventArgs;
use Doctrine\ORM\Event\PreUpdateEventArgs;
use Doctrine\ORM\Events;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;


class DatabaseActivitySubscriber implements EventSubscriberInterface
{
    use ActiveSiteTrait;

    private ParameterBagInterface $params;
    private RequestStack $request;
    private MenuRepository $menuRepository;
    private EntityManagerInterface $entityManager;
    private TokenStorageInterface $tokenStorage;

    public function __construct(
        ParameterBagInterface $parameterBag,
        RequestStack $request,
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->params = $parameterBag;
        $this->request = $request;
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->tokenStorage = $tokenStorage;
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

        if ($entity instanceof OwnerInterface && empty($entity->getOwner())) {
            $entity->setOwner($this->tokenStorage->getToken()->getUser());
        }

        if ($entity instanceof SiteInterface) {
            $this->saveJoinActiveSite($entity);
        }

        if ($entity instanceof ChangeDataDayInterface) {
            $entity->setCreatedAt(new \DateTime('now'));
        }

        if ($entity instanceof IsJoinMenuInterface) {
            $this->newMenu($entity);
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
        foreach ($parentMenuItems as $item) {
            $result .=  $item['url'];
        }

        return $result;
    }

    public function preUpdate(PreUpdateEventArgs $args): void
    {
        $entity = $args->getObject();

        if ($entity instanceof OwnerInterface && empty($entity->getOwner())) {

            $entity->setOwner($this->tokenStorage->getToken()->getUser());
        }

        if ($entity instanceof ChangeDataDayInterface) {
            $dateNow = new \DateTime('now');

            if ($entity instanceof SafeDeleteInterface && $entity->getStatusDelete()) {
                $entity->getDeletedAt($dateNow);
            } else {
                $entity->setUpdatedAt($dateNow);
            }
        }
    }

    private function newMenu(IsJoinMenuInterface $entity): void
    {
        $activeSite = $this->getActiveSite();

        if (empty($activeSite['id']) ||
            (empty($entity->getMenu()) && $this->menuRepository->getMenuLength($activeSite['id']) > 0)) {
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
        } else {
            $menu->setUrl('');
        }
        $menu->setPath($path);
        $menu->setType(Menu::SITE_PAGE_TYPE);
        $this->menuRepository->create($menu, $entity->getMenu());
        $entity->setMenu($menu);
    }



    private function saveJoinActiveSite(SiteInterface $entity): void
    {
        $activeSite = $this->getActiveSite();
        if (!empty($activeSite['id'])) {

            $entity->setSiteId($activeSite['id']);
        }
    }

}