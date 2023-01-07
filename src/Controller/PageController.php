<?php

namespace App\Controller;

use App\Controller\Traits\ActiveTrait;
use App\Controller\Traits\FileUploadTrait;
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
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PageController extends AbstractController
{
    use ActiveTrait, FileUploadTrait;

    private PageRepository $pageRepository;
    private SluggerInterface $slugger;
    private LoggerInterface $logger;

    private const SUCCESS_MESSAGE = 'Page saved';
    private const SUCCESS_SEO_MESSAGE = 'Page saved';
    private const DELETE_MESSAGE = 'Page deleted';
    private const ERROR_MESSAGE = "Error! Page not saved";

    public function __construct(PageRepository $pageRepository, SluggerInterface $slugger, LoggerInterface $logger)
    {
        $this->pageRepository = $pageRepository;
        $this->slugger = $slugger;
        $this->logger = $logger;
    }

    /**
     * @Route("/admin/page", name="app_page_index", methods={"GET"})
     */
    public function index(Request $request, PaginatorInterface $paginator): Response
    {
        $siteId = $this->getActiveSiteId($request->getHost());

        $pagination = $paginator->paginate(
            $this->pageRepository
                ->getAllQueryBuilder($siteId)
                ->getQuery(),
            $request->query->getInt('page', 1),
            $this->getActiveSite($request->getHost())['max_preview_pages'] ?? 5
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
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
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
    public function show(Page $page): Response
    {
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
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);

            } catch (NestedSetsException | NestedSetsMoveUnderSelfException | NestedSetsNodeNotFoundException $e) {
                $this->addFlash("error", $e->getMessage());
                $this->logger->error($e->getMessage());
            } catch (\Throwable $e) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($e->getMessage());
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

    /**
     * @Route("/admin/page/seo/{id}", name="app_page_seo", methods={"GET","POST"})
     */
    public function seo(Request $request, Page $page, SeoRepository $seoRepository): Response
    {
        $siteId = $this->getActiveSiteId($request->getHost());
        $seo = $seoRepository->getByEntity(Page::class, $siteId, $page->getId()) ?? new Seo();

        $default = [];

        if (!empty($page->getImage())) {
            $imageParams = $this->getParameter('image');
            $default['og:image'] = $page->getImage();
            $default['og:image:url'] =
                $request->getSchemeAndHttpHost() .
                $imageParams['load_form_path']['small'] . '/' .
                $page->getImage();

            $default['og:image:width'] = $imageParams['size']['small']['width'];
            $default['og:image:height'] = $imageParams['size']['small']['height'];
            $default['og:image:alt'] = $page->getName();
        }


        $form = $this->createForm(SeoType::class, $seo, [
                'exclude' => ['entity', 'entityId'],
                'default' => $default
            ]
        );

        $seo
            ->setEntity(Page::class)
            ->setEntityId($page->getId())
            ->setSiteId($siteId);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $seoRepository->deleteSubItems($seo);
                $seoRepository->add($seo, true);
                $this->addFlash('success', self::SUCCESS_SEO_MESSAGE);
                return $this->redirectToRoute('app_page_seo', ['id' => $page->getId()]);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->renderForm('page/edit.html.twig', [
            'page' => $page,
            'form' => $form,
        ]);
    }


    /**
     * @Route("/", name="app_page_min", methods={"GET"})
     */
    public function main(Request $request, PaginatorInterface $paginator): Response
    {
        try {
            $siteId = $this->getActiveSiteId($request->getHost());
            $limit = $this->getActiveSite($request->getHost())['max_preview_pages'] ?? 5;
            $query = $this
                ->pageRepository
                ->getAllQueryBuilder($siteId)
                ->andWhere($this->pageRepository->getAlias() . ".isOnMainPage=:isOnMainPage")
                ->setParameter("isOnMainPage", true)
                ->addOrderBy($this->pageRepository->getAlias() . ".id", "DESC")
                ->getQuery();

            $pages = $paginator->paginate(
                $query,
                $request->query->getInt('page', 1),
                $limit
            );
            $page = $this->pageRepository->findOneBy([
                'id' => $this->getActiveSite($request->getHost())['main_page_entity_id'] ?? 0,
                'siteId' => $siteId
            ]);
        } catch (NotFoundHttpException $exception) {

        } catch (\Throwable $exception) {
            throw $exception;
        }

        return $this->render('page/main.html.twig', [
            'pages' => $pages,
            'page' => $page
        ]);
    }

    /**
     * @Route("/pages/{slug}", requirements={"slug"="[a-z0-9\/\-]*"}, name="app_page_detail", methods={"GET"})
     */
    public function detail(Request $request, string $slug): Response
    {
        $siteId = $this->getActiveSiteId($request->getHost());
        $page = $this->pageRepository->getBySlug($siteId, $slug);
        return $this->render('page/detail.html.twig', [
            'page' => $page
        ]);
    }

    public function preview(
        Request            $request,
        Page               $page,
        MenuRepository     $menuRepository,
        PaginatorInterface $paginator
    ): Response {

        $pages = null;

        if ($page->isIsPreview() && $page->getMenu() instanceof NodeInterface) {
            $limit = $this->getActiveSite($request->getHost())['max_preview_pages'] ?? 5;

            $queryBuilder = $this
                ->pageRepository
                ->getQueryBuilder()
                ->innerJoin($this->pageRepository->getAlias() . ".menu", $menuRepository->getAlias());

            $queryBuilder = $menuRepository
                ->getParentsByItemQueryBuilder($page->getMenu(), $page->getPreviewDeep(), $queryBuilder);

            $queryBuilder
                ->orderBy($this->pageRepository->getAlias() . ".id");

            $pages = $paginator->paginate(
                $queryBuilder->getQuery(),
                $request->query->getInt('page', 1),
                $limit
            );
        }


        return $this->render('page/preview.html.twig', [
            'pages' => $pages
        ]);
    }
}

