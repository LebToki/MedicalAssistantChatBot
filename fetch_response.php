<?php
// Replace the database connection details with your own
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpbot";

// Create a new database connection
$conn = new mysqli($servername, $username, $password, $dbname);

// Check the connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Retrieve the latest user message from the database (adjust table and column names as per your structure)
$sql = "SELECT message FROM chatbot ORDER BY id DESC LIMIT 1";
$result = $conn->query($sql);

if ($result->num_rows > 0) {
    $row = $result->fetch_assoc();
    $userMessage = $row['message'];

    // Perform the search algorithm to find the most relevant answer
    $matchedAnswers = searchAnswers($userMessage, $conn);

    // Select the most relevant answer or a set of top-ranked answers
    $topAnswer = $matchedAnswers[0] ?? null; // Get the highest-scoring answer

    // Prepare the response
    $response = $topAnswer ? $topAnswer['response'] : 'Sorry, I couldn\'t find a suitable answer.';
} else {
    $response = 'No user message found.';
}

// Send the response back to the client
echo $response;

// Close the database connection
$conn->close();

function searchAnswers($userMessage, $conn) {
    // Perform the search algorithm to find the most relevant answers from the database
    $sql = "SELECT * FROM chatbot WHERE message LIKE '%$userMessage%' ORDER BY relevance DESC";
    $result = $conn->query($sql);

    $matchedAnswers = [];
    if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $matchedAnswers[] = $row;
        }
    }

    return $matchedAnswers;
}
?>
