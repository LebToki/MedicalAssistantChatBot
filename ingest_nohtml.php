<?php
$directory = __DIR__ . '/training/';
$files = scandir($directory);

// Establish a connection to the database
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpbot";

$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Prepare a query to check for duplicates
$checkDuplicateQuery = "SELECT COUNT(*) FROM chatbot WHERE messages = ? AND response = ?";
$checkDuplicateStmt = $conn->prepare($checkDuplicateQuery);
$checkDuplicateStmt->bind_param("ss", $message, $response);

// Prepare a query to insert new records
$insertQuery = "INSERT INTO chatbot (messages, response) VALUES (?, ?)";
$insertStmt = $conn->prepare($insertQuery);
$insertStmt->bind_param("ss", $message, $response);

// Iterate over the files in the directory
foreach ($files as $file) {
    if (is_file($directory . $file)) {
        $data = json_decode(file_get_contents($directory . $file), true);

        foreach ($data as $item) {
            if (isset($item['input']) && isset($item['output'])) {
                $message = $item['input'];
                $response = $item['output'];

                // Check if the record already exists
                $checkDuplicateStmt->execute();
                $checkDuplicateStmt->store_result();
                $checkDuplicateStmt->bind_result($count);
                $checkDuplicateStmt->fetch();

                if ($count == 0) {
                    // Insert the new record
                    $insertStmt->execute();
                }
            }
        }
    }
}

// Close the statements and the database connection
$checkDuplicateStmt->close();
$insertStmt->close();
$conn->close();
?>
