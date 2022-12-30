<?php

namespace App\Controller;

use App\Entity\FirstInstall;
use App\Form\FirstInstallFormType;
use App\Security\AppCustomAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use http\Client\Request;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\DependencyInjection\ParameterBag\ParameterBagInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;

class FirstInstallController extends AbstractController
{
    /**
     * @Route("/first-install", name="app_first_install")
     */
    public function index(
        \Symfony\Component\HttpFoundation\Request $request,
        UserPasswordHasherInterface               $userPasswordHasher,
        UserAuthenticatorInterface                $userAuthenticator,
        AppCustomAuthenticator                    $authenticator,
        EntityManagerInterface                    $entityManager,
        ParameterBagInterface $params
    ): Response {

        $firstInstallFile = $params->get('kernel.project_dir').DIRECTORY_SEPARATOR.'.first-install';
        $isError = false;

        if (file_exists($firstInstallFile) && !is_writable($firstInstallFile)) {
            $isError = true;
            $this->addFlash('error', "File [ $firstInstallFile ] is not available to write");
        }

        if (!$isError) {

            if (file_exists($firstInstallFile)) {
                $dataExists = $entityManager->getConnection()->executeQuery(
                    "SELECT COUNT(s.id) as `site`, COUNT(u.id) as `user` FROM `site` s, `user` u"
                )->fetchAssociative();


                foreach ($dataExists as $k => $value) {
                    if ($value > 0) {
                        $this->addFlash('error',
                            "Data in [ {$k} ] already exists. Delete it before install" .
                            (file_exists($firstInstallFile) ? " Or remove/rename file $firstInstallFile" : '')
                        );
                        $isError = true;
                    }
                }
            } elseif (!$isError) {
                return $this->redirectToRoute('app_site_index');
            }
        }

        $data = new FirstInstall();
        $form = $this->createForm(FirstInstallFormType::class, $data);
        $form->handleRequest($request);
        $renderParams = [];

        if (!$isError && $form->isSubmitted() && $form->isValid()) {

            $user = $form->get('user')->getData();
            $site = $form->get('site')->getData();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $form->get('user')->getData()->getPassword()
                )
            );

            try {
                $entityManager->persist($site);
                $entityManager->persist($user);
                $entityManager->flush();

                if (file_exists($firstInstallFile)) {
                    unlink($firstInstallFile);
                }

                return $userAuthenticator->authenticateUser(
                    $user,
                    $authenticator,
                    $request
                );
            } catch (\Throwable $exception) {
                throw $exception;
            }

        }

        if (!$isError) {
            $renderParams = ['form' => $form->createView()];
        }

        return $this->render('first_install/index.html.twig', $renderParams);
    }
}
