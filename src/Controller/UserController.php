<?php

namespace App\Controller;

use App\Service\ApiConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    private ApiConnector $apiConnector;
    private SessionInterface $session;

    public function __construct(ApiConnector $apiConnector, RequestStack $requestStack)
    {
        $this->apiConnector = $apiConnector;
        $this->session = $requestStack->getSession();
    }

    #[Route('/profile', name: 'app_profile')]
    public function profile()
    {
        $user = $this->session->get('user');

        if (!$user) {
            return $this->redirectToRoute('app_login');
        }

        return $this->render('user/profile.html.twig', [
            'user' => $user
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout()
    {
        $this->session->clear();
        return $this->redirectToRoute('app_login');
    }
}