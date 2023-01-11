<?php

namespace App\Controller;

use App\Entity\Seo;
use App\Form\SeoType;
use App\Repository\SeoRepository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use App\Service\Interfaces\CacheKeyServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoController extends AbstractController
{
    private PaginatorInterface $paginator;
    private SeoRepository $seoRepository;
    private ActiveSiteServiceInterface $activeSiteService;
    private LoggerInterface $logger;
    private CacheKeyServiceInterface $cacheKeyService;

    private const SUCCESS_MESSAGE = 'SEO saved';
    private const DELETE_MESSAGE = 'SEO deleted';
    private const ERROR_MESSAGE = "Error! SEO not saved";

    public function __construct(
        PaginatorInterface $paginator,
        SeoRepository $seoRepository,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface $logger,
        CacheKeyServiceInterface $cacheKeyService
    ) {
        $this->paginator = $paginator;
        $this->seoRepository = $seoRepository;
        $this->activeSiteService = $activeSiteService;
        $this->cacheKeyService = $cacheKeyService;
        $this->logger = $logger;
    }

    /**
     * @Route("/admin/seo", name="app_seo_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $siteId = $this->activeSiteService->getId();

        $pagination = $this->paginator->paginate(
            $this->seoRepository
                ->getAllQueryBuilder($siteId)
                ->addOrderBy($this->seoRepository->getAlias().".id", "DESC")
                ->getQuery(),

            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5
        );

        return $this->render('seo/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/admin/seo/new", name="app_seo_new", methods={"GET", "POST"})
     */
    public function new(Request $request, SeoRepository $seoRepository): Response
    {
        $seo = new Seo();
        $form = $this->createForm(SeoType::class, $seo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $seoRepository->add($seo, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->renderForm('seo/new.html.twig', [
            'seo' => $seo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/seo/{id}", name="app_seo_show", methods={"GET"})
     */
    public function show(Seo $seo): Response
    {
        return $this->render('seo/show.html.twig', [
            'seo' => $seo,
        ]);
    }

    /**
     * @Route("/admin/seo/edit/{id}", name="app_seo_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Seo $seo, SeoRepository $seoRepository): Response
    {
        $form = $this->createForm(SeoType::class, $seo);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $seoRepository->add($seo, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->renderForm('seo/edit.html.twig', [
            'seo' => $seo,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/seo/delete{id}", name="app_seo_delete", methods={"POST"})
     */
    public function delete(Request $request, Seo $seo, SeoRepository $seoRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$seo->getId(), $request->request->get('_token'))) {
            try {
                $seoRepository->remove($seo, true);
                $this->addFlash('success', self::DELETE_MESSAGE);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
    }

    public function detail($entity, int $siteId, SeoRepository $seoRepository): Response
    {
        $query = $seoRepository
            ->getByEntityQueryBuilder(get_class($entity), $siteId, $entity->getId())
            ->getQuery()
        ;

        $this
            ->cacheKeyService
            ->getQuery($query, 'seo_detail', 'seo_detail_'.$entity->getId())
        ;

        $seo = $query->getOneOrNullResult();

        $items = [];
        if (!empty($seo)) {
            $items = $seo->getItems();
        }

        return $this->renderForm('seo/detail.html.twig', [
            'entity' => $entity,
            'items' => $items
        ]);
    }
}
