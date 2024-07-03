<?php

namespace App\Controller;

use App\Entity\Book;
use App\Repository\BookRepository;
use App\Repository\AuthorRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Service\ImageUploader;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Nelmio\ApiDocBundle\Annotation\Model;
use OpenApi\Attributes as OA;

#[Route('/api', name: 'api_')]
class BookController extends AbstractController
{
    #[Route('/books', name: 'book_index', methods: ['get'])]
    #[OA\Response(
        response: 200,
        description: 'Returns Books list',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Book::class, groups: ['default']))
        )
    )]
    #[OA\Parameter(
        name: 'offset',
        in: 'query',
        description: 'The field used set pagination offset',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'books')]
    public function index(Request $request, BookRepository $bookRepository): JsonResponse
    {
        $offset = max(0, $request->query->getInt('offset', 0));
        $paginator = $bookRepository->getBookPaginator($offset);

        return $this->json($paginator);
    }

    #[Route('/books/author/{surname}', name: 'book_find', methods: ['get'])]
    #[OA\Response(
        response: 200,
        description: 'Returns Books list filtered by Author surname',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Book::class, groups: ['default']))
        )
    )]
    #[OA\Parameter(
        name: 'offset',
        in: 'query',
        description: 'The field used set pagination offset',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'books')]
    public function find(Request $request, string $surname, BookRepository $bookRepository): JsonResponse
    {
        $offset = max(0, $request->query->get('offset', 0));
        $paginator = $bookRepository->findByAuthorSurname($surname, $offset);

        return $this->json($paginator);
    }

    #[Route('/books/{id}', name: 'book_show', methods: ['get'])]
    #[OA\Response(
        response: 200,
        description: 'Returns single Book',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Book::class, groups: ['default']))
        )
    )]
    #[OA\Parameter(
        name: 'offset',
        in: 'query',
        description: 'The field used set pagination offset',
        schema: new OA\Schema(type: 'string')
    )]
    #[OA\Tag(name: 'books')]
    public function show(int $id, BookRepository $bookRepository): JsonResponse
    {
        $book = $bookRepository->find($id);

        if (is_null($book)) {
            return $this->json(['errors' => ['message' => 'Item not found.']], Response::HTTP_NOT_FOUND);
        }

        return $this->json($book);
    }

    #[Route('/books', name: 'book_create', methods: ['post'])]
    #[OA\Response(
        response: 201,
        description: 'Returns created book',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Book::class, groups: ['default']))
        )
    )]
    #[OA\Tag(name: 'books')]
    public function create(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, ImageUploader $imageUploader, AuthorRepository $authorRepository): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $book = new Book();
        $name = $request->getPayload()->get('name');
        $authors = json_decode($request->getPayload()->get('authors'));
        $description = $request->getPayload()->get('description');
        $published = $request->getPayload()->get('published');

        try {
            if (is_null($name) || is_null($authors)) {
                throw new BadRequestHttpException('Emty required fields');
            }

            $book->setName($name);

            if (!is_array($authors)) {
                throw new BadRequestHttpException('Authors should be array of ids');
            }

            foreach ($authors as $authorId) {
                $author = $authorRepository->find($authorId);

                if ($author) {
                    $book->addAuthor($author);
                }
            }


            if (!is_null($description)) {
                $book->setDescription($description);
            }

            $file = $request->files->get('file');

            if (isset($file)) {
                $uploadErrors = $imageUploader->validateImageUpload($request);
                if (count($uploadErrors)) {
                    throw new BadRequestHttpException((string) $uploadErrors);
                }

                $filePath = $imageUploader->upload($file);
                $book->setImage($filePath);
            }


            if (!is_null($published)) {
                $date = \DateTime::createFromFormat('Y', $published);
                $book->setPublishedAt($date);
            }

            $errors = $validator->validate($book);

            if (count($errors)) {
                throw new BadRequestHttpException((string) $errors);
            }

            $entityManager->persist($book);
            $entityManager->flush();


        } catch (\Exception $error) {
            return $this->json(['errors' => ['message' => $error->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($book, Response::HTTP_CREATED);
    }


    #[Route('/books/{id}', name: 'book_update', methods: ['put'])]
    #[OA\Response(
        response: 200,
        description: 'Returns updated book',
        content: new OA\JsonContent(
            type: 'array',
            items: new OA\Items(ref: new Model(type: Book::class, groups: ['default']))
        )
    )]
    #[OA\Tag(name: 'books')]
    public function update(Request $request, ManagerRegistry $doctrine, ValidatorInterface $validator, ImageUploader $imageUploader, AuthorRepository $authorRepository, BookRepository $bookRepository, int $id, ): JsonResponse
    {
        $entityManager = $doctrine->getManager();

        $book = $bookRepository->find($id);

        if (is_null($book)) {
            return $this->json(['errors' => ['message' => 'Item not found.']], Response::HTTP_NOT_FOUND);
        }


        $name = $request->getPayload()->get('name');
        $authors = json_decode($request->getPayload()->get('authors'));
        $description = $request->getPayload()->get('description');
        $published = $request->getPayload()->get('published');

        try {
            if (is_null($name) || is_null($authors)) {
                throw new BadRequestHttpException('Emty required fields');
            }

            $book->setName($name);

            if (!is_array($authors)) {
                throw new BadRequestHttpException('Authors should be array of ids');
            }

            foreach ($book->getAuthors() as $author) {
                if (!in_array($author->getId(), $authors)) {
                    $book->removeAuthor($author);
                }
            }

            foreach ($authors as $authorId) {
                $author = $authorRepository->find($authorId);

                if ($author) {
                    $book->addAuthor($author);
                }
            }


            if (!is_null($description)) {
                $book->setDescription($description);
            } else {
                $book->setDescription(null);
            }

            $file = $request->files->get('file');

            if (isset($file)) {
                $uploadErrors = $imageUploader->validateImageUpload($request);
                if (count($uploadErrors)) {
                    throw new BadRequestHttpException((string) $uploadErrors);
                }

                $filePath = $imageUploader->upload($file);
                $book->setImage($filePath);
            }


            if (!is_null($published)) {
                $date = \DateTime::createFromFormat('Y', $published);
                $book->setPublishedAt($date);
            }

            $errors = $validator->validate($book);

            if (count($errors)) {
                throw new BadRequestHttpException((string) $errors);
            }

            $entityManager->persist($book);
            $entityManager->flush();


        } catch (\Exception $error) {
            return $this->json(['errors' => ['message' => $error->getMessage()]], Response::HTTP_BAD_REQUEST);
        }

        return $this->json($book);
    }
}
