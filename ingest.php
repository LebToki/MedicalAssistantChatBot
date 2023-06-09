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

// Variables to track progress and errors
$totalFiles = count($files) - 2; // Subtract 2 for "." and ".."
$processedFiles = 0;
$errorFiles = [];

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

        // Update progress
        $processedFiles++;
    }
}

// Close the statements and the database connection
$checkDuplicateStmt->close();
$insertStmt->close();
$conn->close();
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Doctor AI Assistant Bot > Ingest Client</title>
    <link rel="icon" type="image/png" href="assets/aibot.png">
    <link rel="stylesheet" href="css/bot.css">
</head>

<body>
    <div id="container">
        <div id="screen">
            <div id="header">
            <img src="/assets/ingest.png" alt="Ingest Icon" style="width:40px;">
            &nbsp; Medical Doctor AI Helper Bot &nbsp; <i class="arrow right"></i>&nbsp;  Ingest Station &nbsp;
                <small style="font-weight: 400; font-size:13px;">
                <a href="index.php" class="pointerlink">
                <i class="arrow left"></i>&nbsp;   
                        Back to the AiBot
                    </a>
                </small>
            </div>


            <div class="progress-bar">
                <div class="progress-bar-fill" style="width: <?php echo ($processedFiles / $totalFiles) * 100; ?>%;"></div>
            </div>

            <h2>Processing Files:</h2>
            <ul>
                <?php foreach ($files as $file) {
                    if (is_file($directory . $file)) {
                        echo '<li' . (in_array($file, $errorFiles) ? ' class="error-file"' : '') . '>' . $file . '</li>';
                    }
                } ?>
            </ul>

            <?php if (count($errorFiles) > 0) : ?>
                <h2>Error Files:</h2>
                <ul>
                    <?php foreach ($errorFiles as $file) {
                        echo '<li>' . $file . '</li>';
                    } ?>
                </ul>
            <?php endif; ?>
        </div>
    </div> <!-- Add the closing div tag for the container -->

</body>

</html>
