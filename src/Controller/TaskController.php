<?php

namespace App\Controller;

// Entity
use App\Entity\Task;
use App\Repository\TaskRepository;
// Services
use App\Service\InputCleaner;
use App\Service\DataValidator;
// Doctrine 
use Doctrine\ORM\EntityManagerInterface;
// Symfony Components
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class TaskController extends AbstractController {
    private $dataValidator;

    public function __construct(DataValidator $dataValidator) {
        $this->dataValidator = $dataValidator;
    }

    // Get tasks list
    #[Route('/tasks', name: 'task_index', methods: ['GET'], requirements: ['page' => Requirement::DIGITS, 'limit' => Requirement::DIGITS])]
    public function index(TaskRepository $taskRepository, Request $request) : JsonResponse {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $tasks = $taskRepository->paginateFindAll($page, $limit);
        return $this->json($tasks, Response::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // Create task
    #[Route('/tasks', name: 'task_create', methods: ['POST'])]
    public function create(Request $request, EntityManagerInterface $em): JsonResponse {

        $data = json_decode($request->getContent(), true);
        $cleanedData = InputCleaner::cleanInput($data);

        $requiredFields = ['title', 'content', 'dueDate', 'isComplete'];
        $validationResponse = $this->dataValidator->validateRequiredFields($cleanedData, $requiredFields);
        if ($validationResponse !== null) {
            return $validationResponse;
        }

        $task = new Task();
        $task->setTitle($cleanedData['title']);
        $task->setContent($cleanedData['content']);
        $task->setDueDate(new \DateTime($cleanedData['dueDate']));
        $task->setIsComplete($cleanedData['isComplete']);
        $task->setAssignedTo($cleanedData['assignedTo']);

        $em->persist($task);
        $em->flush();

        return $this->json($task, Response::HTTP_CREATED, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // Get task by id
    #[Route('/tasks/{id}', name: 'task_show', methods: ['GET'], requirements: ['id' => Requirement::DIGITS])]
    public function show(TaskRepository $taskRepository, int $id): JsonResponse{

        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Tâche non trouvée'], Response::HTTP_NOT_FOUND, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        }

        return $this->json($task, Response::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // Update task by id
    #[Route('/tasks/{id}', name: 'task_update', methods: ['PUT'], requirements: ['id' => Requirement::DIGITS])]
    public function update(Request $request, int $id, TaskRepository $taskRepository, EntityManagerInterface $em): JsonResponse {

        $task = $taskRepository->find($id);
        if (!$task) {
            return $this->json(['error' => 'Tâche non trouvée'], Response::HTTP_NOT_FOUND, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        }

        $data = json_decode($request->getContent(), true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            return $this->json(['error' => 'Invalid JSON'], Response::HTTP_BAD_REQUEST, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        }

        $cleanedData = InputCleaner::cleanInput($data);

        $fieldsToUpdate = [
            'title' => 'setTitle',
            'content' => 'setContent',
            'dueDate' => function($value) use ($task) { $task->setDueDate(new \DateTime($value)); },
            'isComplete' => 'setIsComplete',
            'assignedTo' => 'setAssignedTo'
        ];

        foreach ($fieldsToUpdate as $field => $method) {
            if (isset($cleanedData[$field])) {
                try {
                    if (is_callable($method)) {
                        $method($cleanedData[$field]);
                    } else {
                        $task->$method($cleanedData[$field]);
                    }
                } catch (\Exception $e) {
                    return $this->json(['error' => 'Invalid data for field: ' . $field], Response::HTTP_BAD_REQUEST, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
                }
            }
        }

        $task->setUpdatedAt(new \DateTime('now', new \DateTimeZone('Europe/Paris')));

        $em->persist($task);
        $em->flush();

        return $this->json($task, Response::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    // Delete task by id
    #[Route('/tasks/{id}', name: 'task_delete', methods: ['DELETE'], requirements: ['id' => Requirement::DIGITS])]
    public function delete(int $id, TaskRepository $taskRepository, EntityManagerInterface $em): Response {
        $task = $taskRepository->find($id);

        if (!$task) {
            return $this->json(['error' => 'Tâche non trouvée'], Response::HTTP_NOT_FOUND, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        }

        $em->remove($task);
        $em->flush();

        return $this->json(['message' => 'Tâche supprimée avec succès'], Response::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Get tasks ordered by dueDate ASC with pagination
     *
     * @param Request $request
     * @param TaskRepository $taskRepository
     * 
     * @return JsonResponse with paginated tasks
     */
    #[Route('/tasks/due-date', name: 'task_by_dueDate', methods: ['GET'], requirements: ['page' => Requirement::DIGITS, 'limit' => Requirement::DIGITS])]
    public function findAllByDueDate(Request $request, TaskRepository $taskRepository): JsonResponse {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 5);

        $tasks = $taskRepository->paginateFindAllByDueDate($page, $limit);
        return $this->json($tasks, JsonResponse::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }

    /**
     * Get tasks with advanced search criteria filtering
     *
     * @param Request $request
     * @param TaskRepository $taskRepository
     * 
     * @return JsonResponse
     */
    #[Route('/tasks/search', name: 'task_search', methods: ['GET'], requirements: [
        'page' => Requirement::DIGITS, 
        'limit' => Requirement::DIGITS, 
        'dueDate' => Requirement::DATE_YMD, 
        'isComplete' => Requirement::ASCII_SLUG, // Boolean would be better but we can't use it as a route requirement
        'user' => Requirement::ASCII_SLUG, 
        'keywords' => Requirement::ASCII_SLUG])]
    public function search(Request $request, TaskRepository $taskRepository): JsonResponse {

        $criteria = [
            'user' => $request->query->get('user'),
            'isComplete' => $request->query->get('isComplete'),
            'keywords' => $request->query->get('keywords'),
            'dueDate' => $request->query->get('dueDate'),
        ];

        $cleanedCriteria = array_filter($criteria, function($value) {
            return $value !== null && $value !== '';
        });

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 10);

        try {
            $tasks = $taskRepository->paginateFindByCriteria($cleanedCriteria, $page, $limit);
        } catch (\Exception $e) {
            return $this->json(['error' => 'Erreur lors de la recherche des tâches: ' . $e->getMessage()], JsonResponse::HTTP_BAD_REQUEST);
        }

        if ($tasks->count() === 0) {
            return $this->json(['error' => 'Aucune tâche trouvée'], JsonResponse::HTTP_NOT_FOUND, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
        }

        return $this->json($tasks, JsonResponse::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }
}