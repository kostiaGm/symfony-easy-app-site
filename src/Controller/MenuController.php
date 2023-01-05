<?php

namespace App\Controller;

use App\Entity\Interfaces\NodeInterface;
use App\Entity\Interfaces\StatusInterface;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use App\Entity\Menu;
use App\Form\MenuType;
use App\Repository\MenuRepository;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Contracts\Translation\TranslatorInterface;


class MenuController extends AbstractController
{
    private MenuRepository $menuRepository;
    private EntityManagerInterface $entityManager;
    private ActiveSiteServiceInterface $activeSiteService;
    private LoggerInterface $logger;

    private const SUCCESS_MESSAGE = 'Menu saved';
    private const DELETE_MESSAGE = 'Menu deleted';
    private const ERROR_MESSAGE = "Error! Menu not saved";

    public function __construct(
        MenuRepository $menuRepository,
        EntityManagerInterface $entityManager,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface $logger
    ) {
        $this->menuRepository = $menuRepository;
        $this->entityManager = $entityManager;
        $this->activeSiteService = $activeSiteService;
        $this->logger = $logger;
    }

    /**
     * @Route("/admin/menu", name="menu_admin_index")
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
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
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
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
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                $this->menuRepository->create($menu, $parent);
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
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
        $menuOldUrl = $menu;

        $form = $this->createForm(MenuType::class, $menu);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $menu->setUrl($menu->getTransliteratedUrl());
                $this->menuRepository->updateUrlInSubElements($menu, $menuOldUrl->getUrl());
                $this->entityManager->flush();
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('menu_admin_index');
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->render('menu/edit.html.twig', [
            'form' => $form->createView(),
            'menuItem' => $menu
        ]);
    }


    public function treeMenuAdmin(Request $request, PaginatorInterface $paginator): Response
    {
        $pagination = $paginator->paginate(
            $this->menuRepository
                ->getAllMenuQueryBuilder($this->activeSiteService->getId())
                ->getQuery(),

            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5
        );

        return $this->render(
             'menu/tree_menu_admin.html.twig', [
            'request' => $request,
            'pagination' => $pagination
        ]);
    }

    public function treeMenu(Request $request, ?int $tree): Response
    {
        return $this->render(
            'menu/tree_menu.html.twig', [
            'items' => $this->menuRepository->getAllMenu($this->activeSiteService->get())
        ]);
    }

    public function leftMenu(Request $request)
    {
        return $this->render(
            'menu/left_menu.html.twig', [
            'items' => $this->menuRepository->getAllMenu($this->activeSiteService->getId(), [
                'isLeftMenu' => true
            ]),
            'request' => $request
        ]);
    }

    public function topMenu(Request $request): Response
    {
        $items = $this->menuRepository->getAllMenu($this->activeSiteService->getId(), ['isTopMenu' => true]);
        return $this->render('menu/top_menu.html.twig',[
            'activeSite' => $this->activeSiteService->get(),
            'items' => $items
        ]);
    }

    public function bottomMenu(Request $request): Response
    {
        $items = $this->menuRepository->getAllMenu($this->activeSiteService->getId(), ['isBottomMenu' => true]);
        return $this->render('menu/bottom_menu.html.twig',[
            'activeSite' => $this->activeSiteService->get(),
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
