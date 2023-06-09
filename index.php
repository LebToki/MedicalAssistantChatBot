<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Medical Doctor AI Helper Bot</title>
    <link rel="icon" type="image/png" href="assets/aibot.png">
    <link rel="stylesheet" href="css/bot.css">
</head>

<body>
    <div id="container">
        <div id="dot1"></div>
        <div id="dot2"></div>
        <div id="screen">
            <div id="header">
            <img src="/assets/aibot.png" alt="avatar" style="width:40px;">&nbsp; Medical Doctor AI Assistant Bot
            &nbsp; <small style="font-weight: 400; font-size:13px;">
            
            <a href="ingest.php" class="pointerlink">
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
        // Fetch the response from the server (replace 'fetch_response.php' with your server-side script)
        fetch('fetch_response.php')
            .then(response => response.text())
            .then(data => {
                // Apply the typewriting effect to the response
                var typed = new Typed('#response', {
                    strings: [data],

                    // Other configuration options
                    typeSpeed: 40,
                    backSpeed: 0,
                    showCursor: true,
                    cursorChar: '|',
                    autoInsertCss: true,

                });
            });
    </script>

    <script>
        $(document).ready(function() {
            $("#messages").on("keyup", function() {
                if ($("#messages").val()) {
                    $("#send").css("display", "block");
                } else {
                    $("#send").css("display", "none");
                }
            });
        });

        $("#send").on("click", function(e) {
            var userMessage = $("#messages").val();
            var userMessageHTML = '<div class="chat usermessages">' +
                '<div class="user-avatar"></div>' +
                userMessage +
                '</div>';
            $("#messagedisplaysection").append(userMessageHTML);

            // Scroll to the last input
            $("#messagedisplaysection").scrollTop($("#messagedisplaysection")[0].scrollHeight);

            $.ajax({
                url: "bot.php",
                type: "POST",
                data: {
                    messageValue: userMessage
                },
                success: function(data) {
                    var botResponseHTML = '<div class="chat botmessages">' +
                        '<div class="bot-avatar"></div>' +
                        data +
                        '</div>';
                    $("#messagedisplaysection").append(botResponseHTML);

                    // Scroll to the last input
                    $("#messagedisplaysection").scrollTop($("#messagedisplaysection")[0].scrollHeight);
                }
            });

            $("#messages").val("");
            $("#send").css("display", "none");
        });
    </script>
</body>

</html>