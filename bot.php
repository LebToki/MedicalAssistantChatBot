<?php
require_once "bootstrap.php";
// bot.php
require 'database_config.php'; // DB connection settings

try {
    // Establish the database connection using PDO
    $conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
    $conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    $icon = BASE_URL . 'assets/aibot.png';
    echo '<div class="chat botmessages"><img src="' . $icon . '" alt="avatar" style="width:30px;">&nbsp; Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>';
    die('Connection failed: ' . $e->getMessage());
}

// Define a function to get the chatbot response
function getChatbotResponse($conn, $message)
{
    $stmt = $conn->prepare("SELECT response FROM chatbot WHERE messages LIKE CONCAT('%', :message, '%')");
    $stmt->bindParam(':message', $message);
    $stmt->execute();
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($result) {
        return $result['response'];
    }
    return "Sorry, I can't understand you.";
}

if (isset($_POST['messageValue'])) {
    $userMessage = $_POST['messageValue'];
    $response = getChatbotResponse($conn, $userMessage);

    // Sanitize the user's message for HTML output
    $userMessageHTML = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');

    $icon = BASE_URL . 'assets/aibot.png';
    echo '<div class="chat botmessages"><img src="' . $icon . '" alt="avatar" style="width:30px;">&nbsp; ' . htmlspecialchars($response, ENT_QUOTES, 'UTF-8') . '</div>';
} else {
    $icon = BASE_URL . 'assets/aibot.png';
    echo '<div class="chat botmessages"><img src="' . $icon . '" alt="avatar" style="width:30px;">&nbsp; Error: No message value received.</div>';
}

$conn = null; // Close the database connection
