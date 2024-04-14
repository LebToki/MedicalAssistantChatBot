<?php
	// Determine if SSL is used
	$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || $_SERVER['SERVER_PORT'] == 443;
	
	// Use the right protocol
	$protocol = $isSecure ? 'https://' : 'http://';
	
	// Get the server host
	$host = $_SERVER['HTTP_HOST'];
	
	// Get the current script's directory and normalize it
	$scriptDir = dirname($_SERVER['SCRIPT_NAME']);
	$scriptDir = rtrim($scriptDir, '/') . '/'; // Ensure trailing slash
	
	// Define BASE_URL
	define('BASE_URL', $protocol . $host . $scriptDir);
?>

<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Doctor AI Helper Bot</title>
    <link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/aibot.png">
    <link rel="stylesheet" href="css/bot.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/4.7.0/css/font-awesome.min.css">
</head>

<body>
    <div id="container">
        <div id="dot1"></div>
        <div id="dot2"></div>
        <div id="screen">
            <div id="header">
                <img src="<?php echo BASE_URL; ?>assets/aibot.png" alt="avatar" style="width:40px;">&nbsp; Medical Doctor AI Assistant Bot
                &nbsp; <small style="font-weight: 400; font-size:13px;">

                    <a href="<?php echo BASE_URL; ?>assets/ingest.php" class="pointerlink">
                        <i class="arrow left"></i>&nbsp;
                        Visit the Data Ingest Station
                    </a>
                </small>
            </div>

            <div id="messagedisplaysection">
                <!-- Add an element to display the response -->
                <p id="response"></p>
            </div>

            <div id="userinput">
                <input type="text" name="message" id="messages" placeholder="type your message" autocomplete="off" required>
                <input type="submit" value="Send" id="send" name="send">
            </div>
        </div>

        <div class="px-3 pb-3 pt-2 text-center ">
            <small class="disclaimer">
                This Demo Bot is dumb and may produce inaccurate information about people, places, or facts.
            </small>
        </div>

    </div>



    <!-- Jquery cdn -->
    <script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

    <!-- Include the Typed.js library -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/typed.js/2.0.12/typed.min.js"></script>

    <!-- Add a script to fetch the response from the server and initialize the typewriting effect -->
		<script>
        $(document).ready(function() {
            $("#send").hide(); // Initially hide the send button

            $("#messages").on("keyup", function() {
                $("#send").toggle(!!$(this).val());
            });

            $("#send").on("click", function() {
                var userMessage = $("#messages").val();
                $("#messagedisplaysection").append(
                    `<div class="chat usermessages"><div class="user-avatar"></div>${userMessage}</div>`
                ).scrollTop($("#messagedisplaysection")[0].scrollHeight);

                $.post("bot.php", { messageValue: userMessage }, function(response) {
                    $("#messagedisplaysection").append(
                        `<div class="chat botmessages"><div class="bot-avatar"></div>${response}</div>`
                    ).scrollTop($("#messagedisplaysection")[0].scrollHeight);
                });

                $("#messages").val('');
                $(this).hide();
            });

            // Fetch initial bot response
            fetch('<?php echo BASE_URL; ?>fetch_response.php')
                .then(response => response.text())
                .then(data => {
                    new Typed('#response', {
                        strings: [data],
                        typeSpeed: 40,
                        showCursor: true,
                        cursorChar: '|',
                        autoInsertCss: true,
                    });
                })
                .catch(error => {
                    console.error('Fetch error:', error);
                });
        });
		</script>

</body>

</html>
