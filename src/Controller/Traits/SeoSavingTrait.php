<?php

namespace App\Controller\Traits;

use App\Entity\Page;
use App\Form\SeoType;
use App\Repository\SeoRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

trait SeoSavingTrait
{
    private static string $seoSuccessMessage = 'SEO saved';

    /**
     * @Route("/admin/seo/{id}", name="app_seo", methods={"GET","POST"})
     */
    public function seo(int $id, Request $request, SeoRepository $seoRepository): Response
    {
        $query = $this
            ->pageRepository
            ->getByIdQueryBuilder($id)
            ->getQuery();

        $entity = $query->getOneOrNullResult();

        if (empty($entity)) {
            throw new NotFoundHttpException("Page [ $id ] not found");
        }

        $siteId = $this->activeSiteService->getId();
        $query = $seoRepository
            ->getByEntityQueryBuilder(Page::class, $siteId, $entity->getId())
            ->getQuery()
        ;

        $seo = $query->getOneOrNullResult();
        $default = [];

        if (!empty($entity->getImage())) {
            $imageParams = $this->getParameter('image');
            $default['og:image'] = $entity->getImage();
            $default['og:image:url'] =
                $request->getSchemeAndHttpHost() .
                $imageParams['load_form_path']['small'] . '/' .
                $entity->getImage();

            $default['og:image:width'] = $imageParams['size']['small']['width'];
            $default['og:image:height'] = $imageParams['size']['small']['height'];
            $default['og:image:alt'] = $entity->getName();
        }

        $form = $this->createForm(SeoType::class, $seo, [
                'exclude' => ['entity', 'entityId'],
                'default' => $default
            ]
        );

        $seo
            ->setEntity(self::getEntityClass())
            ->setEntityId($entity->getId())
            ->setSiteId($siteId);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $seoRepository->deleteSubItems($seo);
                $seoRepository->add($seo, true);
                $this->addFlash('success', self::$seoSuccessMessage);
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
}