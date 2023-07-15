<?php

namespace App\Controller;

use App\Controller\Traits\BulkTrait;
use App\Entity\Interfaces\StatusInterface;
use App\Entity\Role;
use App\Form\RoleType;
use App\Repository\RoleRepository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class RoleController extends AbstractController
{

    use BulkTrait;

    private RoleRepository $repository;
    private PaginatorInterface $paginator;
    private ActiveSiteServiceInterface $activeSiteService;


    private const SUCCESS_MESSAGE = 'Role saved';
    private const DELETE_MESSAGE = 'Role deleted';
    private const ERROR_MESSAGE = "Error! Role not saved";
    private const ERROR_DELETE_MESSAGE = "Error! Role not deleted";

    private const RESTORE_MESSAGE = 'Role came back';
    private const RESTORE_ERROR_MESSAGE = 'Error! Role not came back';


    private const BULK_DELETE_MESSAGE = 'Roles deleted';
    private const BULK_DELETE_ERROR_MESSAGE = 'Error! Roles not deleted';
    private const BULK_RESTORE_MESSAGE = 'Roles came back';
    private const BULK_RESTORE_ERROR_MESSAGE = 'Error! Roles not came back';


    public function __construct(
        RoleRepository             $repository,
        PaginatorInterface         $paginator,
        ActiveSiteServiceInterface $activeSiteService
    )
    {
        $this->repository = $repository;
        $this->paginator = $paginator;
        $this->activeSiteService = $activeSiteService;
    }

    /**
     * @Route("/admin/role", name="app_role_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $siteId = $this->activeSiteService->getId();
        $isShowBin = $request->query->get('show') == 'bin';

        $queryBuilder = $this
            ->repository
            ->getAllQueryBuilder($siteId, $isShowBin ? Role::STATUS_DELETED : Role::STATUS_ACTIVE);

        $pagination = $this->paginator->paginate(
            $queryBuilder->getQuery(),
            $request->query->getInt('page', 1),
            $this->activeSiteService->get()['max_preview_pages'] ?? 5,
            ['defaultSortFieldName' => 'r.id', 'defaultSortDirection' => 'desc']
        );

        return $this->render('role/index.html.twig', [
            'pagination' => $pagination,
            'binLength' => $this
                ->repository
                ->getDataLengthInBin($siteId, $isShowBin ? Role::STATUS_ACTIVE : Role::STATUS_DELETED)
        ]);
    }

    /**
     * @Route("/admin/role/new", name="app_role_new", methods={"GET", "POST"})
     */
    public function new(Request $request, RoleRepository $roleRepository): Response
    {
        $role = new Role();
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $role->setStatus(StatusInterface::STATUS_ACTIVE);
            $roleRepository->add($role, true);

            return $this
                ->redirectToRoute( $request->request->get('is_create_new')
                    ? 'app_role_new' :'app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role/new.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/role/{id}", name="app_role_show", methods={"GET"})
     */
    public function show(Role $role): Response
    {
        return $this->render('role/show.html.twig', [
            'role' => $role,
        ]);
    }

    /**
     * @Route("/admin/role/edi/{id}", name="app_role_edit", methods={"GET", "POST"})
     */
    public function edit(Request $request, Role $role, RoleRepository $roleRepository): Response
    {
        $form = $this->createForm(RoleType::class, $role);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $roleRepository->add($role, true);

            return $this
                ->redirectToRoute( $request->request->get('is_create_new')
                    ? 'app_role_new' :'app_role_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('role/edit.html.twig', [
            'role' => $role,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/page/role/{id}", name="app_role_delete", methods={"GET"})
     */
    public function delete(Role $page): Response
    {
        try {
            $page->setStatus(Role::STATUS_DELETED);
            $this->repository->add($page, true);
            $this->addFlash('success', self::DELETE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::ERROR_DELETE_MESSAGE);
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/role/restore/{id}", name="app_role_restore", methods={"GET"})
     */
    public function restore(Role $page): Response
    {
        try {
            $page->setStatus(Role::STATUS_ACTIVE);
            $this->repository->add($page, true);
            $this->addFlash('success', self::RESTORE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::RESTORE_ERROR_MESSAGE);
        }

        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/role/bulk-delete/", name="app_role_bulk_delete", methods={"POST"})
     */
    public function bulkDelete(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_DELETE_MESSAGE,
            self::BULK_DELETE_ERROR_MESSAGE
        );
        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/role/bulk-restore/", name="app_role_bulk_restore", methods={"POST"})
     */
    public function bulkRestore(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_RESTORE_MESSAGE,
            self::BULK_RESTORE_ERROR_MESSAGE,
            Role::STATUS_ACTIVE
        );
        return $this->redirectToRoute('app_role_index', [], Response::HTTP_SEE_OTHER);
    }
}
