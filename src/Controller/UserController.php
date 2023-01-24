<?php

namespace App\Controller;

use App\Controller\Traits\BulkTrait;
use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use App\Service\Interfaces\ActiveSiteServiceInterface;
use Doctrine\Common\Collections\ArrayCollection;
use Knp\Component\Pager\PaginatorInterface;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    use BulkTrait;

    private PaginatorInterface $paginator;
    private UserRepository $repository;

    private ActiveSiteServiceInterface $activeSiteService;
    private LoggerInterface $logger;

    private const SUCCESS_MESSAGE = 'User saved';
    private const DELETE_MESSAGE = 'User deleted';
    private const ERROR_MESSAGE = "Error! User not saved";

    private const ERROR_DELETE_MESSAGE = "Error! User not deleted";

    private const RESTORE_MESSAGE = 'User came back';
    private const RESTORE_ERROR_MESSAGE = 'Error! User not came back';

    private const BULK_DELETE_MESSAGE = 'Users deleted';
    private const BULK_DELETE_ERROR_MESSAGE = 'Error! Users not deleted';
    private const BULK_RESTORE_MESSAGE = 'Users came back';
    private const BULK_RESTORE_ERROR_MESSAGE = 'Error! Users not came back';

    public function __construct(
        PaginatorInterface $paginator,
        UserRepository $repository,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface $logger
    ) {
        $this->paginator = $paginator;
        $this->repository = $repository;
        $this->activeSiteService = $activeSiteService;
        $this->logger = $logger;
    }

    /**
     * @Route("/admin/user", name="app_user_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $isShowBin = $request->query->get('show') == 'bin';

        $limit = $this->activeSiteService->get()['max_preview_pages'] ?? 5;
        $siteId = $this->activeSiteService->getId();
        $queryBuilder = $this
            ->repository
            ->getAllQueryBuilder($siteId, $isShowBin ? User::STATUS_DELETED : User::STATUS_ACTIVE);

        $this->createFormBuilder();

        $query = $queryBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
            'binLength' => $this
                ->repository
                ->getDataLengthInBin($siteId, $isShowBin ? User::STATUS_ACTIVE : User::STATUS_DELETED)
        ]);
    }

    /**
     * @Route("/admin/user/new", name="app_user_new", methods={"GET", "POST"})
     */
    public function new(Request $request): Response
    {
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            try {
                $this->repository->add($user, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);

                return $this->redirectToRoute((
                    $request->request->get('is_create_new') ? 'app_user_new' :'app_user_index'
                    ) , [], Response::HTTP_SEE_OTHER);

            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->renderForm('user/new.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/user/{id}", name="app_user_show", methods={"GET"})
     */
    public function show(User $user): Response
    {
        return $this->render('user/show.html.twig', [
            'user' => $user,
        ]);
    }

    /**
     * @Route("/admin/user/edit/{id}", name="app_user_edit", methods={"GET", "POST"})
     */
    public function edit(
        Request $request,
        User $user,
        UserRepository $repository,
        UserPasswordHasherInterface $userPasswordHasher
    ): Response {
        $form = $this->createForm(UserType::class, $user);
        $oldPassword = $user->getPassword();
        $form->handleRequest($request);
        $oldRoles = new ArrayCollection();

        foreach ($user->getRolesCollection() as $item) {
            $oldRoles->add($item);
        }

        if ($form->isSubmitted() && $form->isValid()) {

            foreach ($oldRoles as $role) {
                if (!$user->getRolesCollection()->contains($role)) {
                    $role->removeUser($user);
                    $repository->add($user);
                }
            }

            foreach ($user->getRolesCollection() as $role) {
                $role->addUser($user);
                $repository->add($user);
            }

            if ($user->getPassword() !== null ) {
                $user->setPassword(
                    $userPasswordHasher->hashPassword(
                        $user,
                        $user->getPassword()
                    )
                );
            } else {
                $user->setPassword($oldPassword);
            }

            try {
                $this->repository->add($user, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);

                return $this->redirectToRoute((
                $request->request->get('is_create_new') ? 'app_user_new' :'app_user_index'
                ) , [], Response::HTTP_SEE_OTHER);


            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }


        return $this->renderForm('user/edit.html.twig', [
            'user' => $user,
            'form' => $form,
        ]);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="app_user_delete", methods={"GET"})
     */
    public function delete(User $user, repository $repository): Response
    {
        try {
            $user->setStatus(User::STATUS_DELETED);
            $this->repository->add($user, true);
            $this->addFlash('success', self::RESTORE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::RESTORE_ERROR_MESSAGE);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/user/delete/{id}", name="app_user_restore", methods={"GET"})
     */
    public function restore(User $user, repository $repository): Response
    {
        try {
            $user->setStatus(User::STATUS_ACTIVE);
            $this->repository->add($user, true);
            $this->addFlash('success', self::RESTORE_MESSAGE);
        } catch (\Throwable $exception) {
            $this->logger->error($exception->getMessage());
            $this->addFlash('error', self::RESTORE_ERROR_MESSAGE);
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/user/bulk-delete/", name="app_user_bulk_delete", methods={"POST"})
     */
    public function bulkDelete(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_DELETE_MESSAGE,
            self::BULK_DELETE_ERROR_MESSAGE
        );
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

    /**
     * @Route("/admin/user/bulk-restore/", name="app_user_bulk_restore", methods={"POST"})
     */
    public function bulkRestore(Request $request): Response
    {
        $this->bulkChange(
            $request,
            self::BULK_RESTORE_MESSAGE,
            self::BULK_RESTORE_ERROR_MESSAGE,
            User::STATUS_ACTIVE
        );
        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }

}
