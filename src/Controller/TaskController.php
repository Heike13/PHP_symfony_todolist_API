<?php

namespace App\Controller;

// Entity
use App\Repository\TaskRepository;

// Symfony Components
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Routing\Requirement\Requirement;

class TaskController extends AbstractController {

    public function __construct() {
    }

    // Get tasks list
    #[Route('/tasks', name: 'task_index', methods: ['GET'], requirements: ['page' => Requirement::DIGITS, 'limit' => Requirement::DIGITS])]
    public function index(TaskRepository $taskRepository, Request $request) : JsonResponse {

        $page = $request->query->getInt('page', 1);
        $limit = $request->query->getInt('limit', 20);

        $tasks = $taskRepository->paginateFindAll($page, $limit);
        return $this->json($tasks, Response::HTTP_OK, [], ['json_encode_options' => JSON_UNESCAPED_UNICODE]);
    }
}