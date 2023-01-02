<?php

namespace App\Controller;

use App\Controller\Traits\ActiveTrait;
use App\Controller\Traits\FileUploadTrait;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Page;
use App\Entity\User;
use App\Form\PageType;
use App\Repository\PageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;


class PageController extends AbstractController
{
    use ActiveTrait, FileUploadTrait;

    private PageRepository $pageRepository;
    private SluggerInterface $slugger;

    public function __construct(PageRepository $pageRepository, SluggerInterface $slugger)
    {
        $this->pageRepository = $pageRepository;
        $this->slugger = $slugger;
    }

    /**
     * @Route("/admin/page", name="app_page_index", methods={"GET"})
     */
    public function index(): Response
    {
        return $this->render('page/index.html.twig', [
            'pages' => $this->pageRepository->findAll(),
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
            $fileName = $this->uploadImage($form);
            $page->setImage($fileName);
            $this->pageRepository->add($page, true);

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
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
    public function edit(Request $request, Page $page): Response
    {
        $form = $this->createForm(PageType::class, $page);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $fileName = $this->uploadImage($form);
            $this->removeImage($page->getImage());
            $this->pageRepository->add($page, true);

            return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
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
        if ($this->isCsrfTokenValid('delete'.$page->getId(), $request->request->get('_token'))) {
            $this->removeImage($page->getImage());
            $this->pageRepository->remove($page, true);
        }

        return $this->redirectToRoute('app_page_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/", name="app_page_min", methods={"GET"})
     */
    public function main(): Response
    {
        return $this->render('page/main.html.twig', [
            'pages' => $this->pageRepository->findAll(),
        ]);
    }

    /**
     * @Route("/technical-works", name="technical_works", methods={"GET"})
     */
    public function technicalWorks(Request $request): Response
    {
        $user = $this->getUser();

        if (($this->getParameter('site')[$request->getHost()]['status']?? false) == StatusInterface::STATUS_ACTIVE
            || (!empty($user) && in_array(User::ROLE_ADMIN, $user->getRoles()))) {
            return $this->redirectToRoute('app_page_min');
        }
        return $this->render('page/technical_works.html.twig');
    }

    /**
     * @Route("/page/{slug}", requirements={"slug"="[a-z0-9\/\-]*"}, name="app_page_detail", methods={"GET"})
     */
    public function detail(Request $request, string $slug): Response
    {
        $page = $this->pageRepository->getBySlug($this->getActiveSiteId($request->getHost()), $slug);
        return $this->render('page/detail.html.twig', [
            'page' => $page
        ]);
    }

}

