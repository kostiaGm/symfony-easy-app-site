<?php

namespace App\Controller;

use App\Controller\Traits\BulkDeleteTrait;
use App\Controller\Traits\BulkTrait;
use App\Controller\Traits\CacheTrait;
use App\Controller\Traits\FileUploadTrait;

use App\Entity\Interfaces\NodeInterface;
use App\Entity\Page;
use App\Entity\PageFilter;
use App\EventSubscriberService\Interfaces\DBSMenuInterface;
use App\Exceptions\NestedSetsException;
use App\Exceptions\NestedSetsMoveUnderSelfException;
use App\Exceptions\NestedSetsNodeNotFoundException;
use App\Form\PageType;
use App\Lib\FilterManager;
use App\Repository\MenuRepository;
use App\Repository\PageRepository;
use App\Repository\repository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormFactoryInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Cache\CacheInterface;


class PageController extends AbstractController
{
    use FileUploadTrait, BulkTrait;

    private PageRepository $repository;
    private SluggerInterface $slugger;
    private LoggerInterface $logger;
    private ActiveSiteServiceInterface $activeSiteService;
    private CacheKeyServiceInterface $cacheKeyService;
    private PaginatorInterface $paginator;
    private CacheInterface $cache;

    private const SUCCESS_MESSAGE = 'Page was saved';
    private const DELETE_MESSAGE = 'Page was deleted';
    private const ERROR_MESSAGE = "Error! Page not saved";
    private const ERROR_DELETE_MESSAGE = "Error! Page not deleted";

    private const RESTORE_MESSAGE = 'Page came back';
    private const RESTORE_ERROR_MESSAGE = 'Error! Page not came back';


    private const BULK_DELETE_MESSAGE = 'Pages deleted';
    private const BULK_DELETE_ERROR_MESSAGE = 'Error! Pages not deleted';
    private const BULK_RESTORE_MESSAGE = 'Pages came back';
    private const BULK_RESTORE_ERROR_MESSAGE = 'Error! Pages not came back';

    public function __construct(
        PageRepository            $repository,
        SluggerInterface           $slugger,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface            $logger,
        CacheKeyServiceInterface   $cacheKeyService,
        PaginatorInterface         $paginator,
        CacheInterface             $cache
    )
    {
        $this->repository = $repository;
        $this->slugger = $slugger;
        $this->activeSiteService = $activeSiteService;
        $this->cacheKeyService = $cacheKeyService;
        $this->logger = $logger;
        $this->paginator = $paginator;
        $this->cache = $cache;
    }

    use BulkTrait;

    /**
     * @Route("/admin/page/", name="app_page_index", methods={"GET", "POST"})
     */
    public function index(Request $request, FormFactoryInterface $formFactory): Response
    {
        $this->denyAccessUnlessGranted(__FUNCTION__);
        $siteId = $this->activeSiteService->getId();

        $pageFilter = new PageFilter();
        $isShowBin = $request->query->get('show') == 'bin';

        $queryBuilder = $this
            ->repository
            ->getAllQueryBuilder($siteId, $isShowBin ? Page::STATUS_DELETED : Page::STATUS_ACTIVE);

        $filterManager = new FilterManager(
            $pageFilter,
            $queryBuilder,
            $this->cache,
            $this->repository->getAlias(),
            $this->activeSiteService->get()['datetime_format'],
            $request->get('filter')
        );

        $filterForm = $filterManager->getForm($formFactory);
        $filterForm->handleRequest($request);

        if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            $url = $filterManager->getUrl('page');
            return $this->redirectToRoute('app_page_index', ['filter' => !empty($url) ? $url : 'w']);
        }

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5,
            ['defaultSortFieldName' => 'p.id', 'defaultSortDirection' => 'desc']
        );

        return $this->render('page/index.html.twig', [
            'pagination' => $pagination,
            'form' => $filterForm->createView(),
            'binLength' => $this
                ->repository
                ->getDataLengthInBin($siteId, $isShowBin ? Page::STATUS_ACTIVE : Page::STATUS_DELETED)
        ]);
    }

    /**
     * @Route("/admin/page/new", name="app_page_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $page = new Page();

        $this->denyAccessUnlessGranted(__FUNCTION__, $page);
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $fileName = $this->uploadImage($form);
                $page->setImage($fileName);
                $this->repository->add($page, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);

                return $this->redirectToRoute((
                $request->request->get('is_create_new') ? 'app_page_new' :'app_page_index'
                ) , [], Response::HTTP_SEE_OTHER);

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

        $this->denyAccessUnlessGranted(__FUNCTION__, $page);
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

                $this->repository->add($page, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);

                if ($request->request->get('is_create_new')) {
                    return $this->redirectToRoute('app_page_new', [], Response::HTTP_SEE_OTHER);
                }

                return $this->redirectToRoute((
                    $request->request->get('is_create_new') ? 'app_page_new' :'app_page_index'
                ) , [], Response::HTTP_SEE_OTHER);

            } catch (NestedSetsException|NestedSetsMoveUnderSelfException|NestedSetsNodeNotFoundException $e) {
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
     * @Route("/admin/page/delete/{id}", name="app_page_delete", methods={"GET"})
     */
    public function delete(Page $page): Response
    {
        try {
            $page->setStatus(Page::STATUS_DELETED);
            $this->repository->add($page, true);
            $this->addFlash('success', self::DELETE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::ERROR_DELETE_MESSAGE);
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/page/restore/{id}", name="app_page_restore", methods={"GET"})
     */
    public function restore(Page $page): Response
    {
        try {
            $page->setStatus(Page::STATUS_ACTIVE);
            $this->repository->add($page, true);
            $this->addFlash('success', self::RESTORE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::RESTORE_ERROR_MESSAGE);
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/page/bulk-delete/", name="app_page_bulk_delete", methods={"POST"})
     */
    public function bulkDelete(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_DELETE_MESSAGE,
            self::BULK_DELETE_ERROR_MESSAGE
        );
        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/page/bulk-restore/", name="app_page_bulk_restore", methods={"POST"})
     */
    public function bulkRestore(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_RESTORE_MESSAGE,
            self::BULK_RESTORE_ERROR_MESSAGE,
            Page::STATUS_ACTIVE
        );
        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /* ******************************** End Admin ******************************************** */

    /* -------------------------------- Front ------------------------------------------------- */


    /**
     * @Route("/{_locale}", name="app_page_main", methods={"GET"}, defaults={"_locale"="en"}, requirements={"_locale"="en|\w{2}"})
     */
    public function main(Request $request): Response
    {
        try {
            $siteId = $this->activeSiteService->getId();
            $queryBuilder = $this->repository->getPreviewOnMainQueryBuilder(
                $siteId,
                $this->activeSiteService->get()['max_preview_pages'] ?? 5
            );

            // Check access
            $this->repository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());

            $queryPages = $queryBuilder->getQuery();
            // Set/get in cache
            $this->cacheKeyService->getQuery($queryPages);

            //$this->denyAccessUnlessGranted('app_page_main', $queryBuilder);

            $id = $this->activeSiteService->get()['main_page_entity_id'] ?? 0;
            $queryBuilder = $this->repository->getByIdQueryBuilder($id);

            // Check access
            $this->repository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());
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
     * @Route("/{_locale}/pages/{slug}", requirements={"slug"="[a-z0-9\/\-]*"}, name="app_page_detail", methods={"GET"})
     */
    public function detail(string $slug): Response
    {
        $queryBuilder = $this->repository->getBySlugQueryBuilder(
            $this->activeSiteService->getId(),
            $slug
        );

        // Check access
        $this->repository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());


        // Set/get to cache
        $query = $queryBuilder->getQuery();
        $this->cacheKeyService->getQuery($query);
        $page = $query->getOneOrNullResult();

        if ($page === null) {
            throw new NotFoundHttpException("Page [ {$slug} ] not found");
        }

        $this->denyAccessUnlessGranted(__FUNCTION__, $page);

        return $this->render('page/detail.html.twig', [
            'page' => $page
        ]);
    }

    public function preview(
        Request        $request,
        Page           $page,
        MenuRepository $menuRepository
    ): Response
    {

        $pages = null;

        if ($page->isIsPreview() && $page->getMenu() instanceof NodeInterface) {
            $limit = $this->activeSiteService->get()['max_preview_pages'] ?? 5;

            $queryBuilder = $this
                ->repository
                ->getQueryBuilder()
                ->innerJoin($this->repository->getAlias() . ".menu", $menuRepository->getAlias());

            $queryBuilder = $menuRepository
                ->getParentsByItemQueryBuilder(
                    $page->getMenu(),
                    $page->getPreviewDeep(),
                    $queryBuilder,
                    false
                );

            $queryBuilder->addSelect($menuRepository->getAlias());

            $queryBuilder
                ->orderBy($this->repository->getAlias() . ".id");

            // Check access
            $this->repository->getUsersIdsByMyGroup($queryBuilder, $this->getUser());

            // Set/get to cache
            $query = $queryBuilder->getQuery();
            $this->cacheKeyService->getQuery($query, 'app_page_preview', '_page_id_' . $page->getId());

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
}

