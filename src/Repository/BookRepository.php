<?php

namespace App\Repository;

use App\Entity\Book;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Book>
 */
class BookRepository extends ServiceEntityRepository
{
    public const BOOKS_PER_PAGE = 3;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Book::class);
    }

    public function getBookPaginator(int $offset): Paginator
    {
        $query = $this->createQueryBuilder('b')
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(self::BOOKS_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);

    }

    /**
     * @return Book[] Returns an array of Book objects
     */
    public function findByAuthorSurname(string $value, int $offset): Paginator
    {
        $query = $this->createQueryBuilder('b');
        // 
        $query->leftJoin('b.authors', 'a')
            ->andWhere($query->expr()->like('a.surname', ':val'))
            ->setParameter('val', "%{$value}%")
            ->orderBy('b.id', 'ASC')
            ->setMaxResults(self::BOOKS_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery()
        ;

        return new Paginator($query);
    }
}
