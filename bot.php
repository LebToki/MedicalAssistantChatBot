<?php
// bot.php

// Database connection details
$servername = "localhost";
$username = "root";
$password = "";
$dbname = "phpbot";

// Establish the database connection
$conn = mysqli_connect($servername, $username, $password, $dbname);

// Check if the connection was successful
if ($conn) {
    if (isset($_POST['messageValue'])) {
        // Sanitize the user's message for database query
        $userMessageDB = mysqli_real_escape_string($conn, $_POST['messageValue']);
        
        // Sanitize the user's message for HTML output
        $userMessageHTML = htmlspecialchars($_POST['messageValue'], ENT_QUOTES, 'UTF-8');

        // Perform the database query
        $query = "SELECT response FROM chatbot WHERE messages LIKE '%$userMessageDB%'";
        $result = mysqli_query($conn, $query);

        // Check if a matching response was found
        if (mysqli_num_rows($result) > 0) {
            // Fetch the response from the query result
            $response = mysqli_fetch_assoc($result)['response'];

            // Return the response
            echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; ' . $response . '</div>';
        } else {
            // No matching response found
            echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Sorry, I can\'t understand you.</div>';
        }
    } else {
        // No message value received
        echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Error: No message value received.</div>';
    }

    // Close the database connection
    mysqli_close($conn);
} else {
    // Database connection failed
    echo '<div class="chat botmessages"><img src="/assets/aibot.png" alt="avatar" style="width:30px;">&nbsp; Connection failed: ' . mysqli_connect_error() . '</div>';
}
