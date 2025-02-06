<?php

namespace App\Controller;

use App\Service\ApiConnector;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpFoundation\Request;

class BookController extends AbstractController
{
    private ApiConnector $apiConnector;
    private SessionInterface $session;

    public function __construct(ApiConnector $apiConnector, RequestStack $requestStack)
    {
        $this->apiConnector = $apiConnector;
        $this->session = $requestStack->getSession();
    }

    #[Route('/book/add', name: 'app_book_add', methods: ['GET', 'POST'])]
    public function addBook(Request $request)
    {
        $token = $this->session->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }
        $authorsData = $this->apiConnector->request('GET', '/authors', ['query' => ['limit' => 100]], $token);
        $authors = $authorsData['items'] ?? [];
        if ($request->isMethod('POST')) {
            $data = [
                'title' => $request->request->get('title'),
                'release_date' => $request->request->get('release_date'),
                'description' => $request->request->get('description'),
                'isbn' => $request->request->get('isbn'),
                'format' => $request->request->get('format'),
                'number_of_pages' => (int) $request->request->get('number_of_pages'),
                'author' => ['id' => $request->request->get('author_id')]
            ];

            try {
                $response = $this->apiConnector->request('POST', '/books', ['json' => $data], $token);
                if (isset($response['id'])) {
                    $this->addFlash('success', 'Book added successfully!');
                    return $this->redirectToRoute('app_authors');
                } else {
                    $this->addFlash('error', 'Failed to add book.');
                }
            } catch (\Exception $e) {
                $this->addFlash('error', 'Error: ' . $e->getMessage());
            }
        }

        return $this->render('book/book_add.html.twig', ['authors' => $authors]);
    }

    #[Route('/book/delete/{id}/author/{author_id}', name: 'app_book_delete', methods: ['POST'])]
    public function deleteBook(int $id, int $author_id)
    {
        $token = $this->session->get('token');

        if (!$token) {
            return $this->redirectToRoute('app_login');
        }

        $this->apiConnector->request('DELETE', '/books/' . $id, [], $token);

        $this->addFlash('success', 'Book deleted successfully.');

        return $this->redirectToRoute('app_author_view', ['id' => $author_id]);
    }
}
