<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\UserFilter;
use App\Form\UserType;
use App\Lib\FilterManager;
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
    private PaginatorInterface $paginator;
    private UserRepository $userRepository;

    private ActiveSiteServiceInterface $activeSiteService;
    private LoggerInterface $logger;

    private const SUCCESS_MESSAGE = 'User saved';
    private const DELETE_MESSAGE = 'User deleted';
    private const ERROR_MESSAGE = "Error! User not saved";

    public function __construct(
        PaginatorInterface $paginator,
        UserRepository $userRepository,
        ActiveSiteServiceInterface $activeSiteService,
        LoggerInterface $logger
    ) {
        $this->paginator = $paginator;
        $this->userRepository = $userRepository;
        $this->activeSiteService = $activeSiteService;
        $this->logger = $logger;
    }

    /**
     * @Route("/admin/user", name="app_user_index", methods={"GET"})
     */
    public function index(Request $request): Response
    {
        $limit = $this->activeSiteService->get()['max_preview_pages'] ?? 5;
        $queryBuilder = $this->userRepository->getQueryBuilderWithSiteId($this->activeSiteService->getId());

        $userFilter = new UserFilter();
        $filterManager = new FilterManager(
            $userFilter,
            $queryBuilder
        );

        $this->createFormBuilder();

        $filterData = $filterManager->getFilterItems();

        $query = $queryBuilder->getQuery();

        $pagination = $this->paginator->paginate(
            $query,
            $request->query->getInt('page', 1),
            $limit
        );

        return $this->render('user/index.html.twig', [
            'pagination' => $pagination,
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
                $this->userRepository->add($user, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);;
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
        UserRepository $userRepository,
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
                    $userRepository->add($user);
                }
            }

            foreach ($user->getRolesCollection() as $role) {
                $role->addUser($user);
                $userRepository->add($user);
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
                $this->userRepository->add($user, true);
                $this->addFlash('success', self::SUCCESS_MESSAGE);
                return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
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
     * @Route("/admin/user/delete/{id}", name="app_user_delete", methods={"POST"})
     */
    public function delete(Request $request, User $user, UserRepository $userRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$user->getId(), $request->request->get('_token'))) {
            try {
                $userRepository->remove($user, true);
                $this->addFlash('success', self::DELETE_MESSAGE);
            } catch (\Throwable $exception) {
                $this->addFlash("error", self::ERROR_MESSAGE);
                $this->logger->error($exception->getMessage());
            }
        }

        return $this->redirectToRoute('app_user_index', [], Response::HTTP_SEE_OTHER);
    }
}
