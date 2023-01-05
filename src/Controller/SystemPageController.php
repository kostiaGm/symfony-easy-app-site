<?php

namespace App\Controller;

use App\Entity\Interfaces\StatusInterface;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SystemPageController extends AbstractController
{
    private const ERRORS = [
        'Unknown error',
        'Error site configuration file',

    ];

    /**
     * @Route("/system-page/{id}", name="app_system_page")
     */
    public function index(int $id): Response
    {
        return $this->render('system_page/index.html.twig', [
            'id' => $id,
            'message' => self::ERRORS[$id] ?? self::ERRORS[0]
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
        return $this->render('system_page/technical_works.html.twig');
    }
}
