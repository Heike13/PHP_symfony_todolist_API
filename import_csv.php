<?php

use Doctrine\DBAL\DriverManager;
use Doctrine\DBAL\Exception;

require 'vendor/autoload.php';

$connectionParams = [
    'dbname' => 'todolist',
    'user' => 'symfony',
    'password' => 'symfony',
    'host' => 'database',
    'driver' => 'pdo_mysql',
];

try {
    echo "Connecting to the database...\n";
    $conn = DriverManager::getConnection($connectionParams);
    $conn->connect();
    echo "Connected to the database.\n";

    $csvFile = fopen('/var/www/html/migrations/db/creative_webdev_tasks.csv', 'r');
    if ($csvFile === false) {
        throw new Exception('Could not open CSV file.');
    }
    echo "CSV file opened successfully.\n";

    // Ignorer la ligne d'en-tête
    fgetcsv($csvFile);

    $conn->beginTransaction();

    while (($data = fgetcsv($csvFile, 1000, ',')) !== false) {
        $title = $data[1];
        $content = $data[2];
        $due_date = $data[3];
        $is_complete = $data[4];
        $assigned_to = $data[5];
        $created_at = $data[6];
        $updated_at = $data[7];

        try {
            $conn->insert('task', [
                'title' => $title,
                'content' => $content,
                'due_date' => $due_date,
                'is_complete' => $is_complete,
                'assigned_to' => $assigned_to,
                'created_at' => $created_at,
                'updated_at' => $updated_at,
            ]);
        } catch (Exception $e) {
            echo "Failed to insert task: " . $e->getMessage() . "\n";
        }
    }

    fclose($csvFile);
    $conn->commit();
    echo "CSV data successfully imported.\n";
    echo "URL to access the application: http://localhost:8080/\n";
    
} catch (Exception $e) {
    if (isset($conn) && $conn->isTransactionActive()) {
        $conn->rollBack();
    }
    echo 'Error: ' . $e->getMessage();
}