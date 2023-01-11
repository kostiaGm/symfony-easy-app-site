<?php

namespace App\Controller;

use App\Controller\Traits\ActiveTrait;
use App\Controller\Traits\FileUploadTrait;
use App\Controller\Traits\SeoSavingTrait;
use App\Entity\Interfaces\NodeInterface;
use App\Entity\Page;
use App\Entity\Seo;
use App\EventSubscriberService\Interfaces\DBSMenuInterface;
use App\Exceptions\NestedSetsException;
use App\Exceptions\NestedSetsMoveUnderSelfException;
use App\Exceptions\NestedSetsNodeNotFoundException;
use App\Form\PageType;
use App\Form\SeoType;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Repository\SeoRepository;
use App\Service\CacheKeyService;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;


class PageController extends AbstractController
{
    use FileUploadTrait, SeoSavingTrait;

    private PageRepository $pageRepository;
    private SluggerInterface $slugger;
    private LoggerInterface $logger;
    private ActiveSiteServiceInterface $activeSiteService;
    private CacheKeyServiceInterface $cacheKeyService;
    private PaginatorInterface $paginator;
    private CacheInterface $cache;

    private const SUCCESS_MESSAGE = 'Page saved';
    private const DELETE_MESSAGE = 'Page deleted';
    private const ERROR_MESSAGE = "Error! Page not saved";

    public function __construct(
        PageRepository $pageRepository,
        SluggerInterface $slugger,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface $logger,
        CacheKeyServiceInterface $cacheKeyService,
        PaginatorInterface $paginator,
        CacheInterface $cache
    ) {
        $this->pageRepository = $pageRepository;
        $this->slugger = $slugger;
        $this->activeSiteService = $activeSiteService;
        $this->cacheKeyService = $cacheKeyService;
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->cache = $cache;
    }

    /**
     * @Route("/admin/page", name="app_page_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $query = $this->pageRepository
            ->getAllQueryBuilder($this->activeSiteService->getId())
            ->getQuery();


        //$this->cacheKeyService->getQuery($query);


        $pagination = $this->paginator->paginate(
            $this->pageRepository
                ->getAllQueryBuilder($this->activeSiteService->getId())
                ->getQuery()
                ->useQueryCache(true),
            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5
        );

        return $this->render('page/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/admin/page/new", name="app_page_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $page = new Page();

        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = $this->uploadImage($form);
                $page->setImage($fileName);
                $this->pageRepository->add($page, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $e) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($e->getMessage());
                throw $e;
            }
        }

        return $this->renderForm('page/new.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/page/{id}", name="app_page_show", methods={"GET"})
     */
    public function show(int $id): Response
    {
        $query = $this
            ->pageRepository
            ->getByIdQueryBuilder($id)
            ->getQuery();

        $this->cacheKeyService->getQuery($query);
        $page = $query->getOneOrNullResult();

        if (empty($page)) {
            throw new NotFoundHttpException("Page [ $id ] not found");
        }

        return $this->render('page/show.html.twig', [
            'page' => $page,
        ]);
    }

    /**
     * @Route("/admin/page/edit/{id}", name="app_page_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request          $request,
        Page             $page,
        MenuRepository   $menuRepository,
        DBSMenuInterface $dBSMenu
    ): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $oidMenu = $page->getMenu();

        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {

            try {
                if (empty($oidMenu)) {
                    $dBSMenu->create($page);
                } elseif (!empty($page->getMenu()) && !empty($oidMenu) && $page->getMenu()->getId() != $oidMenu->getId()) {
                    $menuRepository->move($oidMenu, $page->getMenu());
                }

                if (!empty($request->files->get('page')['uploadImage'])) {
                    $fileName = $this->uploadImage($form);
                    $this->removeImage($page->getImage());
                    $page->setImage($fileName);
                }

                $this->pageRepository->add($page, true);

                $cacheItemsKey = CacheKeyService::get(CacheKeyService::setKeys(
                    $this->activeSiteService->getId(),
                    $this->activeSiteService->getDomain(),
                    $this->getUser()->getId(),
                    null,
                    'app_page_detail'
                ),  'app_page_preview', '_page_id_'.$page->getId());

                //
                $cacheDetailKey = CacheKeyService::get(CacheKeyService::setKeys(
                    $this->activeSiteService->getId(),
                    $this->activeSiteService->getDomain(),
                    $this->getUser()->getId(),
                    null,
                    'app_page_detail'
                ));

                $this->cache->delete($cacheItemsKey);
                $this->cache->delete($cacheDetailKey);

                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);

            } catch (NestedSetsException | NestedSetsMoveUnderSelfException | NestedSetsNodeNotFoundException $e) {
                $this->addFlash("error", $e->getMessage());
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($e->getMessage());
                throw $e;
            }
        }

        return $this->renderForm('page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/page/delete/{id}", name="app_page_delete", methods={"POST"})
     */
    public function delete(Request $request, Page $page): Response
    {
        if ($this->isCsrfTokenValid('delete' . $page->getId(), $request->request->get('_token'))) {
            try {
                $this->removeImage($page->getImage());
                $this->pageRepository->remove($page, true);
                $this->addFlash('success', self::DELETE_MESSAGE);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /* ******************************** End Admin ******************************************** */

    /* -------------------------------- Front ------------------------------------------------- */

    /**
     * @Route("/", name="app_page_main", methods={"GET"})
     */
    public function main(Request $request): Response
    {
        try {
            $siteId = $this->activeSiteService->getId();
            $queryBuilder = $this->pageRepository->getPreviewOnMainQueryBuilder(
                $siteId,
                $this->activeSiteService->get()['max_preview_pages'] ?? 5
            );

            // Check access
            $this->pageRepository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());

            $queryPages = $queryBuilder->getQuery();
            // Set/get in cache
            $this->cacheKeyService->getQuery($queryPages);

            //$this->denyAccessUnlessGranted('app_page_main', $queryBuilder);

            $id = $this->activeSiteService->get()['main_page_entity_id'] ?? 0;
            $queryBuilder = $this->pageRepository->getByIdQueryBuilder($id);

            // Check access
            $this->pageRepository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());
            $query = $queryBuilder->getQuery();

            // Save/get in cache
            $this->cacheKeyService->getQuery($query);

            $page = $query->getOneOrNullResult();

        } catch (\Throwable $exception) {
            throw $exception;
        }

        return $this->render('page/main.html.twig', [
            'pages' => $queryPages->getResult(),
            'page' => $page
        ]);
    }

    /**
     * @Route("/pages/{slug}", requirements={"slug"="[a-z0-9\/\-]*"}, name="app_page_detail", methods={"GET"})
     */
    public function detail(string $slug): Response
    {
        $queryBuilder = $this->pageRepository->getBySlugQueryBuilder (
            $this->activeSiteService->getId(),
            $slug
        );

        // Check access
        $this->pageRepository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());


        // Set/get to cache
        $query = $queryBuilder->getQuery();
        $this->cacheKeyService->getQuery($query);
        $page = $query->getOneOrNullResult();

        if ($page === null) {
            throw new NotFoundHttpException("Page [ {$slug} ] not found");
        }

        return $this->render('page/detail.html.twig', [
            'page' => $page
        ]);
    }

    public function preview(
        Request            $request,
        Page               $page,
        MenuRepository     $menuRepository
    ): Response {

        $pages = null;

        if ($page->isIsPreview() && $page->getMenu() instanceof NodeInterface) {
            $limit =  $this->activeSiteService->get()['max_preview_pages'] ?? 5;

            $queryBuilder = $this
                ->pageRepository
                ->getQueryBuilder()
                ->innerJoin($this->pageRepository->getAlias() . ".menu", $menuRepository->getAlias());

            $queryBuilder = $menuRepository
                ->getParentsByItemQueryBuilder($page->getMenu(), $page->getPreviewDeep(), $queryBuilder, false);

            $queryBuilder
                ->orderBy($this->pageRepository->getAlias() . ".id");

            // Check access
            $this->pageRepository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());

            // Set/get to cache
            $query = $queryBuilder->getQuery();
            $this->cacheKeyService->getQuery($query, 'app_page_preview', '_page_id_'.$page->getId());

            $pages = $this->paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                $limit
            );
        }


        return $this->render('page/preview.html.twig', [
            'pages' => $pages
        ]);
    }
    /* ******************************** End Front ******************************************** */

    private static function getEntityClass(): ?string
    {
        return Page::class;
    }
}

