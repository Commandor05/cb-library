<?php

namespace App\Repository;

use App\Entity\Author;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Author>
 */
class AuthorRepository extends ServiceEntityRepository
{
    public const AUTHORS_PER_PAGE = 3;
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Author::class);
    }

    public function getAuthorPaginator(int $offset): Paginator
    {
        $query = $this->createQueryBuilder('a')
            ->orderBy('a.id', 'ASC')
            ->setMaxResults(self::AUTHORS_PER_PAGE)
            ->setFirstResult($offset)
            ->getQuery();

        return new Paginator($query);

    }
}
