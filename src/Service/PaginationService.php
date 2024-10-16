<?php

namespace App\Service;

use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\ORM\EntityManagerInterface;

class PaginationService
{
    private $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * Paginate the results of a DQL query with a given page and limit
     * 
     * @param string $dql (Doctrine Query Language)
     * @param array $parameters empty array by default
     * @param int $page
     * @param int $limit
     *
     * @return Paginator object includes the paginated result
     */
    public function paginate(string $dql, array $parameters = [], int $page = 1, int $limit = 10): Paginator
    {
        $query = $this->entityManager->createQuery($dql)
            ->setFirstResult($limit * ($page - 1))
            ->setMaxResults($limit);

        foreach ($parameters as $key => $value) {
            $query->setParameter($key, $value);
        }

        return new Paginator($query);
    }
}