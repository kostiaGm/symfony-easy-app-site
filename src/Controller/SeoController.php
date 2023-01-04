<?php

namespace App\Controller;

use App\Entity\Seo;
use App\Form\SeoType;
use App\Repository\SeoRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SeoController extends AbstractController
{
    /**
     * @Route("/admin/seo", name="app_seo_index", methods={"GET"})
     */
    public function index(SeoRepository $seoRepository): Response
    {
        return $this->render('seo/index.html.twig', [
            'seos' => $seoRepository->findAll(),
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
            $seoRepository->add($seo, true);

            return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
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
            $seoRepository->add($seo, true);

            return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
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
            $seoRepository->remove($seo, true);
        }

        return $this->redirectToRoute('app_seo_index', [], Response::HTTP_SEE_OTHER);
    }

    public function detail($entity, int $siteId, SeoRepository $seoRepository): Response
    {
        $seo = $seoRepository->getByEntity(get_class($entity), $siteId, $entity->getId());
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
