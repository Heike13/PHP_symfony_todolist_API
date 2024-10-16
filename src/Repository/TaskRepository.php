<?php

namespace App\Repository;

use App\Entity\Task;
use App\Service\PaginationService;
use DateTime;
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

     /**
     * Search for tasks based on advanced criteria 
     * (user, isComplete, keywords, dueDate)
     *
     * @param array $criteria
     * @param int $page
     * @param int $limit
     *
     * @return Paginator
     */
    public function paginateFindByCriteria(array $criteria, $page, $limit): Paginator {
        $qb = $this->createQueryBuilder('t');
        $parameters = [];

        if (!empty($criteria['user'])) {
            $qb->andWhere('t.assignedTo = :user');
            $parameters['user'] = $criteria['user'];
        }

        if (!empty($criteria['isComplete'])) {
            $isComplete = filter_var($criteria['isComplete'], FILTER_VALIDATE_BOOLEAN);
            $qb->andWhere('t.isComplete = :isComplete');
            $parameters['isComplete'] = $isComplete;
        }

        if (!empty($criteria['keywords'])) {
            $qb->andWhere('t.title LIKE :keywords OR t.content LIKE :keywords');
            $parameters['keywords'] = '%' . $criteria['keywords'] . '%';
        }

        if (!empty($criteria['dueDate'])) {
            try {
                $dueDate = $criteria['dueDate'];
                if (preg_match('/^\d{4}$/', $dueDate)) {
                    // Format YYYY
                    $qb->andWhere('SUBSTRING(t.dueDate, 1, 4) = :year');
                    $parameters['year'] = $dueDate;
                } elseif (preg_match('/^\d{4}-\d{2}$/', $dueDate)) {
                    // Format YYYY-MM
                    list($year, $month) = explode('-', $dueDate);
                    $qb->andWhere('SUBSTRING(t.dueDate, 1, 4) = :year AND SUBSTRING(t.dueDate, 6, 2) = :month');
                    $parameters['year'] = $year;
                    $parameters['month'] = $month;
                } elseif (preg_match('/^\d{4}-\d{2}-\d{2}$/', $dueDate)) {
                    // Format YYYY-MM-DD
                    $dueDate = new \DateTime($dueDate);
                    $qb->andWhere('t.dueDate = :dueDate');
                    $parameters['dueDate'] = $dueDate;
                } else {
                    throw new \InvalidArgumentException('Invalid due date format.');
                }
            } catch (\Exception $e) {
                throw new \InvalidArgumentException('Invalid due date format.');
            }
        }

        $qb->orderBy('t.dueDate', 'ASC');

        $dql = $qb->getDQL();
        return $this->paginationService->paginate($dql, $parameters, $page, $limit);
    }
}


// S'il faut plusieurs mots-clés à rechercher dans le titre et le contenu de la tâche
        //
        // if (!empty($criteria['keywords'])) {
        //     $keywords = explode(' ', $criteria['keywords']);
        //     $keywordConditions = $qb->expr()->orX();
    
        //     foreach ($keywords as $index => $keyword) {
        //         $keywordParam = 'keyword' . $index;
        //         $keywordConditions->add($qb->expr()->orX(
        //             $qb->expr()->like('t.title', ':' . $keywordParam),
        //             $qb->expr()->like('t.content', ':' . $keywordParam)
        //         ));
        //         $parameters[$keywordParam] = '%' . $keyword . '%';
        //     }
    
        //     $qb->andWhere($keywordConditions);
        // }