<?php

namespace App\Controller;

use App\Controller\Traits\ActiveTrait;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\StatusInterface;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;
use Twig\Environment;

class MenuController extends AbstractController
{
    use ActiveTrait;

    private MenuRepository $menuRepository;
    private EntityManagerInterface $entityManager;

    public function __construct(MenuRepository $menuRepository, EntityManagerInterface $entityManager)
    {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/admin/menu", name="menu_admin_index")
     */
    public function index(): Response
    {
        return $this->render('menu/index.html.twig');
    }

    /**
     * @Route("/admin/menu/new", name="menu_admin_new_menu")
     */
    public function newRoot(Request $request): Response
    {
        $menu = new Menu();
        $menu->setStatus(StatusInterface::STATUS_ACTIVE);
        $menu->setCreatedAt(new \DateTime('now'));

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $menu->setUrl($menu->getTransliteratedUrl());
                $this->menuRepository->create($menu);
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                throw $exception;
                $this->addFlash("Error", "Error");
            }
        }

        return $this->render('menu/edit.html.twig', [
            'form' => $form->createView()
        ]);
    }

    /**
     * @Route("/admin/menu/new-sub-menu/{id}", name="menu_admin_new_sub_menu")
     */
    public function newSubMenu(Request $request, Menu $parent): Response
    {
        $menu = new Menu();
        $menu->setStatus(StatusInterface::STATUS_ACTIVE);
        $menu->setCreatedAt(new \DateTime('now'));

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $menu->setUrl($menu->getTransliteratedUrl());
                $menu->setPath($parent->getUrl());
                $this->menuRepository->create($menu, $parent);

                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                throw $exception;
                $this->addFlash("Error", "Error");
            }
        }

        return $this->render('menu/edit.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $parent
        ]);
    }

    /**
     * @Route("/admin/menu/edit/{id}", name="menu_admin_edit")
     */
    public function edit(Request $request, Menu $menu): Response
    {
        $menu->setStatus(StatusInterface::STATUS_ACTIVE);
        $menu->setUpdatedAt(new \DateTime('now'));
        $menuOldUrl = $menu->getUrl();

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $menu->setUrl($menu->getTransliteratedUrl());
                $this->menuRepository->updateUrlInSubElements($menu, $menuOldUrl);
                $this->entityManager->flush();

                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                throw $exception;
                $this->addFlash("Error", "Error");
            }
        }

        return $this->render('menu/edit.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $menu
        ]);
    }


    public function treeMenuAdmin(): Response
    {
        return $this->render(
             'menu/tree_menu_admin.html.twig', [
            'items' => $this->menuRepository
                ->getAllQueryBuilder()
                ->getQuery()
                ->getResult()
        ]);
    }

    public function treeMenu(Request $request, ?int $tree): Response
    {
        return $this->render(
            'menu/tree_menu.html.twig', [
            'items' => $this->menuRepository->getAllMenu($this->getActiveSiteId($request->getHost()), $tree)
        ]);
    }

    public function leftMenu(Request $request)
    {
        return $this->render(
            'menu/left_menu.html.twig', [
            'items' => $this->menuRepository->getAllMenu($this->getActiveSiteId($request->getHost()))
        ]);
    }

    public function topMenu(Request $request, int $rootId): Response
    {
        $menu = $this->entityManager->find(Menu::class, $rootId);
        $items = [];
        if ($menu) {
            $items = $this->menuRepository
                ->getAllSubItemsQueryBuilder($menu)
                ->andWhere($this->menuRepository->getAlias().".lvl=:lvl")
                ->setParameter('lvl', $menu->getLvl() + 1)
                ->getQuery()
                ->getResult()
            ;
        }

        return $this->render('menu/top_menu.html.twig',[
            'activeSite' => $this->getParameter('site')[$request->getHost()] ?? '',
            'items' => $items
        ]);
    }

    public function breadcrumbs(NodeInterface $node, MenuRepository $menuRepository): Response
    {
        return $this->render('menu/breadcrumbs.html.twig', [
            'items' => $menuRepository->getParentsByItemQueryBuilder($node)->getQuery()->getArrayResult()
        ]);
    }
}
