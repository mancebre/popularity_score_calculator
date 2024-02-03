<?php

// src/Repository/SearchResultRepository.php

namespace App\Repository;

use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use App\Entity\SearchResult;

class SearchResultRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, SearchResult::class);
    }

    public function findOneByTerm($term)
    {
        return $this->createQueryBuilder('s')
            ->andWhere('s.term = :term')
            ->setParameter('term', $term)
            ->setMaxResults(1)
            ->getQuery()
            ->getOneOrNullResult();
    }
}
