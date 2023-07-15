<?php

namespace App\Controller;

use App\Entity\Gallery;
use App\Entity\GallerySetting;
use App\Form\GallerySettingAdminType;
use App\Form\GallerySettingType;
use App\Form\GalleryType;
use App\Repository\GalleryRepository;
use App\Repository\GallerySettingRepository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SettingController extends AbstractController
{
    private const SUCCESS_MESSAGE = 'Settings was saved';
    private const DELETE_MESSAGE = 'Settings was deleted';
    private const ERROR_MESSAGE = "Error! Settings wasn't saved";

    private PaginatorInterface $paginator;
    private ActiveSiteServiceInterface $activeSiteService;

    public function __construct(
        ActiveSiteServiceInterface $activeSiteService,
        PaginatorInterface $paginator
    ) {
        $this->paginator = $paginator;
        $this->activeSiteService = $activeSiteService;
    }


    /**
     * @Route("/setting", name="app_setting")
     */
    public function index(): Response
    {

        return $this->render('setting/index.html.twig', [
            'controller_name' => 'SettingController',
        ]);
    }

    /**
     * @Route("/setting-gallerty", name="app_setting_gallery")
     */
    public function gallery(
        Request $request,
        GallerySettingRepository $gallerySettingRepository,
        ?int $id = null
    ): Response {

        $gallerySetting = $gallerySetting ?? $gallerySettingRepository->findOneByGallery(null) ?? new GallerySetting();

        //$form = $this->createForm(GallerySettingType::class, $gallerySetting);
        $form = $this->createForm(GallerySettingAdminType::class, $gallerySetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $gallerySettingRepository->add($gallerySetting, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);

                return $this->redirectToRoute((
                $request->request->get('is_create_new') ? 'app_setting_gallery' :'app_gallery_index'
                ) , [], Response::HTTP_SEE_OTHER);

            } catch (\Throwable $e) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($e->getMessage());
                throw $e;
            }
        }

        $queryBuilder = $gallerySettingRepository->getQueryBuilder();

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5,
            ['defaultSortFieldName' => "{$gallerySettingRepository->getAlias()}.id", 'defaultSortDirection' => 'desc']
        );

        return $this->renderForm('setting/gallery.html.twig', [
            'pagination' => $pagination,
            'form' => $form,
        ]);
    }
}
