<?php
require 'database_config.php';

try {
    // Create database connection using PDO
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $conn->beginTransaction(); // Start transaction
} catch (PDOException $e) {
    die('Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
}

$directory = __DIR__ . '/training/';
$files = array_filter(scandir($directory), function ($file) use ($directory) {
    return is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
});

$checkDuplicateStmt = $conn->prepare("SELECT COUNT(*) FROM chatbot WHERE messages = :message AND response = :response");
$insertStmt = $conn->prepare("INSERT INTO chatbot (messages, response) VALUES (:message, :response)");

foreach ($files as $file) {
    $data = json_decode(file_get_contents($directory . $file), true);
    if (json_last_error() !== JSON_ERROR_NONE) {
        continue; // Skip if data is not valid JSON
    }

    foreach ($data as $item) {
        if (!isset($item['input'], $item['output'])) {
            continue; // Skip if essential data is missing
        }

        $message = $item['input'];
        $response = $item['output'];

        $checkDuplicateStmt->bindParam(':message', $message);
        $checkDuplicateStmt->bindParam(':response', $response);
        $checkDuplicateStmt->execute();
        $count = $checkDuplicateStmt->fetchColumn();

        if ($count == 0) {
            $insertStmt->bindParam(':message', $message);
            $insertStmt->bindParam(':response', $response);
            if (!$insertStmt->execute()) {
                $conn->rollBack(); // Rollback if an insert fails
                die("Failed to insert data: " . htmlspecialchars($insertStmt->errorInfo()[2], ENT_QUOTES, 'UTF-8'));
            }
        }
    }
}

$conn->commit(); // Commit the transaction
