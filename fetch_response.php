<?php
    require 'database_config.php';
    
    // Create database connection
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    
    // Check database connection
    if ($conn->connect_error) {
        die('<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Connection failed: ' . $conn->connect_error . '</div>');
    }
    
    // Function to search for answers
    function searchAnswers($userMessage, $conn) {
        $sql = "SELECT * FROM chatbot WHERE messages LIKE ? ORDER BY relevance DESC";
        $stmt = $conn->prepare($sql);
        $searchTerm = "%$userMessage%";
        $stmt->bind_param("s", $searchTerm);
        $stmt->execute();
        $result = $stmt->get_result();
        
        $matchedAnswers = [];
        while ($row = $result->fetch_assoc()) {
            $matchedAnswers[] = $row;
        }
        return $matchedAnswers;
    }
    
    // Retrieve the latest user message
    $sql = "SELECT messages FROM chatbot ORDER BY id DESC LIMIT 1";
    $result = $conn->query($sql);
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        $userMessage = $row['messages'];
        
        // Perform the search algorithm to find the most relevant answer
        $matchedAnswers = searchAnswers($userMessage, $conn);
        
        // Select the most relevant answer or a set of top-ranked answers
        $response = $matchedAnswers[0]['response'] ?? 'Sorry, I could’t find a suitable answer.';
    } else {
        $response = 'No user message found.';
    }
    
    // Send the response back to the client
    echo $response;
    
    // Close the database connection
    $conn->close();
?>
