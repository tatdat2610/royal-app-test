<?php

namespace App\Controller;

use App\Service\ApiConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\RequestStack;

class LoginController extends AbstractController
{
    private ApiConnector $apiConnector;
    private SessionInterface $session;

    public function __construct(ApiConnector $apiConnector, RequestStack $requestStack)
    {
        $this->apiConnector = $apiConnector;
        $this->session = $requestStack->getSession();
    }

    #[Route('/login', name: 'app_login')]
    public function login(Request $request)
    {
        $token = $this->session->get('token');
        if ($token) {
            return $this->redirectToRoute('app_authors');
        }

        if ($request->isMethod('POST')) {
            $email = $request->request->get('email');
            $password = $request->request->get('password');

            $data = $this->apiConnector->login($email, $password);

            if (isset($data['token_key'])) {
                $this->session->set('token', $data['token_key']);
                $this->session->set('user', $data['user']);

                return $this->redirectToRoute('app_profile');
            }

            $this->addFlash('error', 'Invalid credentials');
        }

        return $this->render('login/index.html.twig', [
            'controller_name' => 'LoginController',
        ]);
    }

    #[Route('/logout', name: 'app_logout')]
    public function logout(SessionInterface $session)
    {
        $session->clear();
        return $this->redirectToRoute('app_login');
    }
}