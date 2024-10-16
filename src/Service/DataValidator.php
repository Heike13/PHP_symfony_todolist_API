<?php

namespace App\Service;

use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DataValidator
{
    /**
     * Validate required fields even if database already does so with constraints
     *
     * @param array $data Les données à valider
     * @param array $requiredFields Les champs requis
     * @return JsonResponse|null Une réponse JSON en cas d'erreur, sinon null
     */
    public function validateRequiredFields(array $data, array $requiredFields): ?JsonResponse
    {
        foreach ($requiredFields as $field) {
            if (!isset($data[$field]) || $data[$field] === '') {
                return new JsonResponse(['error' => 'Missing required fields: ' . $field], Response::HTTP_BAD_REQUEST);
            }
        }
        return null;
    }
}