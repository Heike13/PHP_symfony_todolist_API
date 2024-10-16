<?php

namespace App\Service;

class InputCleaner
{
    /**
     * Clean input data by trimming and stripping tags
     * Ignore keys 'createdAt' and 'updatedAt' wich are not user input
     *
     * @param array $data Les données à nettoyer
     * @return array Les données nettoyées
     */
    public static function cleanInput(array $data): array {
        $cleanedData = array_map(function ($item) {
            if (is_string($item)) {
                return trim(strip_tags($item));
            }
            return $item;
        }, $data);

        return $cleanedData;
    }
}