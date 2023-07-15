<?php

namespace App\Controller;

use App\Entity\GallerySetting;
use App\Form\GallerySettingType;
use App\Repository\GallerySettingRepository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/admin/gallery/setting")
 */
class GallerySettingController extends AbstractController
{
    private PaginatorInterface $paginator;
    private ActiveSiteServiceInterface $activeSiteService;

    public function __construct(
        PaginatorInterface $paginator,
        ActiveSiteServiceInterface $activeSiteService
    )
    {
        $this->activeSiteService = $activeSiteService;
        $this->paginator = $paginator;
    }


    /**
     * @Route("/", name="app_gallery_setting_index", methods={"GET"})
     */
    public function index(Request $request, GallerySettingRepository $gallerySettingRepository): Response
    {
        $queryBuilder = $gallerySettingRepository
            ->getQueryBuilder()
            ->leftJoin($gallerySettingRepository->getAlias().'.gallery', 'gst')
            ->addSelect('gst')
        ;

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5,
            ['defaultSortFieldName' => $gallerySettingRepository->getAlias().'.id', 'defaultSortDirection' => 'desc']
        );

        return $this->render('gallery_setting/index.html.twig', [
            'pagination' => $pagination,
        ]);
    }

    /**
     * @Route("/new", name="app_gallery_setting_new", methods={"GET", "POST"})
     */
    public function new(Request $request, GallerySettingRepository $gallerySettingRepository): Response
    {
        $gallerySetting = new GallerySetting();
        $form = $this->createForm(GallerySettingType::class, $gallerySetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallerySettingRepository->add($gallerySetting, true);

            return $this->redirectToRoute('app_gallery_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gallery_setting/new.html.twig', [
            'gallery_setting' => $gallerySetting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_gallery_setting_show", methods={"GET"})
     */
    public function show(GallerySetting $gallerySetting): Response
    {
        return $this->render('gallery_setting/show.html.twig', [
            'gallery_setting' => $gallerySetting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="app_gallery_setting_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, GallerySetting $gallerySetting, GallerySettingRepository $gallerySettingRepository): Response
    {
        $form = $this->createForm(GallerySettingType::class, $gallerySetting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $gallerySettingRepository->add($gallerySetting, true);

            return $this->redirectToRoute('app_gallery_setting_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('gallery_setting/edit.html.twig', [
            'gallery_setting' => $gallerySetting,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/{id}", name="app_gallery_setting_delete", methods={"POST"})
     */
    public function delete(Request $request, GallerySetting $gallerySetting, GallerySettingRepository $gallerySettingRepository): Response
    {
        if ($this->isCsrfTokenValid('delete' . $gallerySetting->getId(), $request->request->get('_token'))) {
            $gallerySettingRepository->remove($gallerySetting, true);
        }

        return $this->redirectToRoute('app_gallery_setting_index', [], Response::HTTP_SEE_OTHER);
    }
}
