<?php
// bot.php
	require 'database_config.php';  // Assume this file contains your DB connection settings

// Establish the database connection using mysqli
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	
	// Check if the connection was successful
	if ($conn->connect_error) {
		echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Connection failed: ' . $conn->connect_error . '</div>';
		die('Connection failed: ' . $conn->connect_error);
	}
	
	// Define a function to get the chatbot response
	function getChatbotResponse($conn, $message) {
		$stmt = $conn->prepare("SELECT response FROM chatbot WHERE messages LIKE CONCAT('%', ?, '%')");
		$stmt->bind_param("s", $message);
		$stmt->execute();
		$result = $stmt->get_result();
		
		if ($result->num_rows > 0) {
			return $result->fetch_assoc()['response'];
		}
		return "Sorry, I can't understand you.";
	}
	
	if (isset($_POST['messageValue'])) {
		$userMessage = $_POST['messageValue'];
		$response = getChatbotResponse($conn, $userMessage);
		
		// Sanitize the user's message for HTML output
		$userMessageHTML = htmlspecialchars($userMessage, ENT_QUOTES, 'UTF-8');
		
		echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; ' . htmlspecialchars($response, ENT_QUOTES, 'UTF-8') . '</div>';
	} else {
		echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Error: No message value received.</div>';
	}

// Close the database connection
	$conn->close();
