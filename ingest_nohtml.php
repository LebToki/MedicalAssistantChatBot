<?php
    $directory = __DIR__ . '/training/';
    $files = array_filter(scandir($directory), function($file) use ($directory) {
        return is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
    });
    
    require 'database_config.php';
    
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        die("Connection failed: " . $conn->connect_error);
    }
    
    $conn->autocommit(FALSE); // Start transaction
    
    $checkDuplicateStmt = $conn->prepare("SELECT COUNT(*) FROM chatbot WHERE messages = ? AND response = ?");
    $insertStmt = $conn->prepare("INSERT INTO chatbot (messages, response) VALUES (?, ?)");
    
    foreach ($files as $file) {
        $data = json_decode(file_get_contents($directory . $file), true);
        if (!$data) {
            continue; // Skip if data is not valid JSON
        }
        
        foreach ($data as $item) {
            if (!isset($item['input'], $item['output'])) {
                continue; // Skip if essential data is missing
            }
            
            $message = $item['input'];
            $response = $item['output'];
            
            $checkDuplicateStmt->bind_param("ss", $message, $response);
            $checkDuplicateStmt->execute();
            $checkDuplicateStmt->store_result();
            $checkDuplicateStmt->bind_result($count);
            $checkDuplicateStmt->fetch();
            
            if ($count == 0) {
                $insertStmt->bind_param("ss", $message, $response);
                if (!$insertStmt->execute()) {
                    $conn->rollback(); // Rollback if an insert fails
                    die("Failed to insert data: " . $insertStmt->error);
                }
            }
        }
    }
    
    $conn->commit(); // Commit the transaction
    $checkDuplicateStmt->close();
    $insertStmt->close();
    $conn->close();
?>
