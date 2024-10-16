<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpKernel\Event\ExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\HttpKernel\Exception\HttpExceptionInterface;

class ExceptionListener
{
    public function onKernelException(ExceptionEvent $event)
    {
        $exception = $event->getThrowable();

        // On vérifie si c'est une route non trouvée
        if ($exception instanceof NotFoundHttpException) {
            $response = new JsonResponse([
                'error' => 'Route inexistante'
            ], 404);

            // On modifie la réponse de l'événement
            $event->setResponse($response);
        }
    }
}
