<?php

namespace App\Controller;

use App\Service\ApiConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;

class AuthorController extends AbstractController
{
    private ApiConnector $apiConnector;
    private SessionInterface $session;

    public function __construct(ApiConnector $apiConnector, RequestStack $requestStack)
    {
        $this->apiConnector = $apiConnector;
        $this->session = $requestStack->getSession();
    }

    #[Route('/authors', name: 'app_authors')]
    public function index(Request $request)
    {
        $token = $this->session->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        $data = $this->apiConnector->request('GET', '/authors', [
            'query' => [
                'limit' => $limit,
                'page' => $page
            ]
        ], $token);

        return $this->render('author/index.html.twig', [
            'controller_name' => 'AuthorController',
            'authors' => $data['items'],
            'total_pages' => $data['total_pages'],
            'current_page' => $data['current_page'],
        ]);
    }

    #[Route('/author/{id}', name: 'app_author_view')]
    public function viewAuthor(int $id)
    {
        $token = $this->session->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $author = $this->apiConnector->request('GET', sprintf('/authors/%s', $id), [], $token);

        return $this->render('author/author_view.html.twig', [
            'author' => $author
        ]);
    }

    #[Route('/author/delete/{id}', name: 'app_author_delete', methods: ['POST'])]
    public function deleteAuthor(int $id)
    {
        $token = $this->session->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $this->apiConnector->request('DELETE', sprintf('/authors/%s', $id), [], $token);

        $this->addFlash('success', 'Author deleted successfully.');

        return $this->redirectToRoute('app_authors');
    }
}
