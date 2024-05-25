<?php
	require 'database_config.php';
	
	try {
		// Create database connection using PDO
		$conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
	} catch (PDOException $e) {
		die('<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8') . '</div>');
	}
	
	// Function to search for answers
	function searchAnswers($userMessage, $conn) {
		$sql = "SELECT * FROM chatbot WHERE messages LIKE :searchTerm";
		$stmt = $conn->prepare($sql);
		$searchTerm = "%" . $userMessage . "%";
		$stmt->bindParam(':searchTerm', $searchTerm);
		$stmt->execute();
		return $stmt->fetchAll(PDO::FETCH_ASSOC);
	}
	
	// Retrieve the latest user message
	$sql = "SELECT messages FROM chatbot ORDER BY id DESC LIMIT 1";
	$stmt = $conn->query($sql);
	$result = $stmt->fetch(PDO::FETCH_ASSOC);
	
	if ($result) {
		$userMessage = $result['messages'];
		
		// Perform the search algorithm to find the most relevant answer
		$matchedAnswers = searchAnswers($userMessage, $conn);
		
		// Select the most relevant answer or a set of top-ranked answers
		$response = $matchedAnswers[0]['response'] ?? 'Sorry, I couldn’t find a suitable answer.';
	} else {
		$response = 'No user message found.';
	}
	
	// Send the response back to the client
	echo htmlspecialchars($response, ENT_QUOTES, 'UTF-8');
	
	// Close the database connection
	$conn = null;
?>
