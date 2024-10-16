<?php

namespace App\Repository;

use App\Entity\Task;
use App\Service\PaginationService;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;
use Doctrine\ORM\Tools\Pagination\Paginator;

/**
 * @extends ServiceEntityRepository<Task>
 */
class TaskRepository extends ServiceEntityRepository {

    private $paginationService;

    public function __construct(ManagerRegistry $registry, PaginationService $paginationService)
    {
        parent::__construct($registry, Task::class);
        $this->paginationService = $paginationService;
    }

    /**
     * Get all tasks with pagination
     *
     * @param integer $page
     * @param integer $limit
     * 
     * @return Paginator
     */
    public function paginateFindAll(int $page, int $limit): Paginator {
        $dql = $this->createQueryBuilder('t')
            ->orderBy('t.id', 'ASC')
            ->getDQL();

        return $this->paginationService->paginate($dql, [], $page, $limit);
    }


    /**
     * Get all tasks ordered by due date with pagination
     *
     * @param int $page
     * @param int $limit
     *
     * @return Paginator object includes the paginated result
     */
    public function paginateFindAllByDueDate($page = 1, $limit = 10): Paginator {
        $dql = $this->createQueryBuilder('t')
            ->orderBy('t.dueDate', 'ASC')
            ->getDQL();

        return $this->paginationService->paginate($dql, [], $page, $limit);
    }
}
