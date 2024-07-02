<?php

namespace App\Controller;

use App\Entity\Author;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

#[Route('/api', name: 'api_')]
class AuthorController extends AbstractController
{
    #[Route('/authors', name: 'author_index', methods: ['get'])]
    public function index(Request $request, AuthorRepository $authorRepository): JsonResponse
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $authorRepository->getAuthorPaginator($offset);

        return $this->json($paginator);
    }

    #[Route('/authors', name: 'author_create', methods: ['post'])]
    public function create(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator): JsonResponse
    {
        $entityManager = $doctrine->getManager();
        $requestBody = json_decode($request->getContent(), true);
        $author = new Author();

        try {
            if (!isset($requestBody['name']) || !isset($requestBody['surname'])) {
                throw new BadRequestHttpException('Emty required fields');
            }

            $author->setName($requestBody['name']);
            $author->setSurname($requestBody['surname']);

            if (isset($requestBody['second_name'])) {
                $author->setSecondName($requestBody['second_name']);
            }

            $errors = $validator->validate($author);

            if (count($errors)) {
                throw new BadRequestHttpException((string) $errors);
                ;
            }

            $entityManager->persist($author);
            $entityManager->flush();

        } catch (\Exception $error) {
            return $this->json(['errors' => ['message' => $error->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($author, Response::HTTP_CREATED);
    }
}
