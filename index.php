<?php
require_once "bootstrap.php";
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
    <style>
    :root {
        --color-primary: #19c37d;
        --color-secondary: #715fde;
        --color-error: #ef4146;
        --gradient-primary: linear-gradient(90deg, #a29bd4, #989fdd);
        --text-primary: #202123;
        --text-default: #353740;
        --text-secondary: #6e6e80;
        --text-disabled: #acacbe;
        --text-error: var(--color-error);

        --font-size-small: 12px;
        --font-size-medium: 16px;
        --font-size-large: 20px;
        --spacing-small: 8px;
        --spacing-medium: 16px;
        --spacing-large: 24px;
    }

    * {
        padding: 0;
        margin: 0;
        font-family: sans-serif;
        box-sizing: border-box;
    }

    html {
        font-size: calc(14px + (17 - 14) * ((100vw - 320px) / (1600 - 320)));
    }

    /* Container */
    #container {
        height: 100vh;
        width: 100%;
        position: relative;
        display: grid;
        place-items: center;
        background: #000;
        overflow: hidden;
    }

    /* Header */
    #header {
        height: 80px;
        width: 100%;
        position: absolute;
        top: 0;
        left: 0;
        background: #000;
        display: flex;
        place-items: center;
        font-size: 25px;
        color: #fff;
        font-weight: bold;
        text-shadow: 1px 1px 20x #000000a1;
    }

    /* Navigation Button/Link */
    .pointerlink {
        appearance: none;
        background-color: #2ea44f;
        border: 1px solid rgba(27, 31, 35, .15);
        border-radius: 6px;
        box-shadow: rgba(27, 31, 35, .1) 0 1px 0;
        box-sizing: border-box;
        color: #fff;
        cursor: pointer;
        display: inline-block;
        font-family: -apple-system, system-ui, "Segoe UI", Helvetica, Arial, sans-serif, "Apple Color Emoji", "Segoe UI Emoji";
        font-size: 14px;
        font-weight: 600;
        line-height: 20px;
        padding: 6px 16px;
        position: relative;
        text-align: center;
        text-decoration: none;
        user-select: none;
        -webkit-user-select: none;
        touch-action: manipulation;
        vertical-align: middle;
        white-space: nowrap;
    }

    .pointerlink:hover,
    .pointerlink:focus {
        filter: brightness(90%);
        outline: none;
        /* Only remove outlines if replacing with another focus indicator */
    }

    .pointerlink:active {
        filter: brightness(85%);
        box-shadow: rgba(225, 228, 232, .2) 0 1px 0 inset;
    }

    @media (max-width: 600px) {
        :root {
            --text-primary-size: 14px;
        }

        #header {
            padding: var(--spacing-small);
            font-size: var(--text-primary-size);
        }
    }

    /* Arrows index */
    .arrow {
        border: solid #fff;
        border-width: 0 3px 3px 0;
        display: inline-block;
        padding: 3px;
    }

    .right {
        transform: rotate(-45deg);
        -webkit-transform: rotate(-45deg);
    }

    .left {
        transform: rotate(135deg);
        -webkit-transform: rotate(135deg);
    }

    .up {
        transform: rotate(-135deg);
        -webkit-transform: rotate(-135deg);
    }

    .down {
        transform: rotate(45deg);
        -webkit-transform: rotate(45deg);
    }

    /* The AIBOT Screen */
    #screen {
        height: 900px;
        width: 1440px;
        border-radius: 25px;
        box-shadow: 3px 3px 15px rgba(0, 0, 0, 0.2);
        overflow: hidden;
    }

    /* Disclaimer */
    .disclaimer {
        color: #acacbe;
        font-size: 13px;
    }

    .dark .dark\:bg-\[\#444654\] {
        --tw-bg-opacity: 1;
        background-color: rgba(68, 70, 84, var(--tw-bg-opacity));
    }

    #messagedisplaysection {
        height: 450px;
        width: 100%;
        position: absolute;
        left: 0;
        top: 100px;
        padding: 0 20px;
        overflow-y: auto;
				color: #fff!important;
    }

    .chat {
        min-height: 40px;
        max-width: 80%;
        padding: 15px;
        border-radius: 10px;
        margin: 15px 0;
        display: flex;
        align-items: center;
    }

    .botmessages {
        background-color: rgba(52, 53, 65, 1);
        color: #fff;
    }

    #messagecontainer {
        height: auto;
        width: 100%;
        display: flex;
        justify-content: flex-end;
    }

    #userinput {
        height: 50px;
        width: 90%;
        position: absolute;
        left: 5%;
        bottom: 3%;
        background: white;
        border-radius: 10px;
        overflow: hidden;
        box-shadow: 2px 2px 8px rgba(0, 0, 0, 0.1);
    }

    #messages {
        height: 50px;
        width: 80%;
        position: absolute;
        left: 0;
        border: none;
        outline: none;
        background: transparent;
        padding: 0px 15px;
        font-size: 17px;
    }

    #send {
        height: 50px;
        width: 24%;
        position: absolute;
        right: 0;
        border: none;
        outline: none;
        display: grid;
        place-items: center;
        color: white;
        background: #17e782;
        font-size: 20px;
        cursor: pointer;
    }

    .usermessages {
        color: #fff;
        align-items: right;
    }

    .user-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-right: 10px;
        background-image: url("assets/user.png");
    }

    .bot-avatar {
        width: 30px;
        height: 30px;
        border-radius: 50%;
        margin-left: 10px;
        background-image: url("assets/aibot.png");
    }

    .chat-message {
        display: flex;
        align-items: flex-start;
        margin-bottom: 10px;
    }

    .avatar {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        margin-right: 10px;
    }

    .user-message .message-content {
        background-image: url('assets/user.png');
        width: 35px;
        /* background-color: #17e782; */
        color: #fff;
        border-radius: 15px 15px 15px 0;
        padding: 10px;
        margin-right: 10px;
        align-self: flex-start;
    }

    .bot-message .message-content {
        /* background-color: #f6f6f6; */
        background-image: url('assets/aibot.png');
        width: 35px;
        color: #333;
        border-radius: 15px 15px 0 15px;
        padding: 10px;
        margin-left: 10px;
        align-self: flex-end;
    }

    /* Data Ingest Station */
    .progress-bar {
        width: 100%;
        background-color: #f0f0f0;
        border-radius: 5px;
        margin-bottom: 10px;
    }

    .progress-bar-fill {
        height: 20px;
        background-color: #19c37d;
        border-radius: 5px;
    }

    .error-file {
        color: #ef4146;
        font-weight: bold;
    }

    /* Styles for small screens (e.g., smartphones) */
    @media only screen and (max-width: 600px) {
        #container {
            padding: 20px;
        }

        #screen {
            height: 100%;
            width: 100%;
            border-radius: 15px;
        }

        #header {
            font-size: 20px;
        }

        #messagedisplaysection {
            height: 300px;
            top: 60px;
        }

        .chat {
            max-width: 80%;
        }

        #userinput {
            height: 40px;
            width: 90%;
            bottom: 10px;
        }

        #messages {
            width: 70%;
            font-size: 14px;
        }

        #send {
            width: 28%;
            font-size: 16px;
        }

        .user-avatar,
        .bot-avatar {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            margin-left: 5px;
        }
    }

    /* Styles for iPads */
    @media only screen and (min-width: 768px) and (max-width: 1024px) {
        #screen {
            /* Adjust the height and width for iPads */
            height: 600px;
            width: 800px;
        }

        #container {
            padding: 20px;
        }

        #header {
            font-size: 20px;
        }

        #messagedisplaysection {
            height: 300px;
            top: 60px;
        }

        .chat {
            max-width: 80%;
        }

        #userinput {
            height: 40px;
            width: 90%;
            bottom: 10px;
        }

        #messages {
            width: 70%;
            font-size: 14px;
        }

        #send {
            width: 28%;
            font-size: 16px;
        }

        .user-avatar,
        .bot-avatar {
            width: 20px;
            height: 20px;
            margin-right: 5px;
            margin-left: 5px;
        }
    }
    </style>
</head>

<body>
    <div id="container">
        <div id="header">
            <img src="<?php echo BASE_URL; ?>assets/aibot.png" alt="avatar" style="width:40px;">
            <span>Medical Doctor AI Assistant Bot</span>&nbsp;&nbsp;&nbsp;
					<small style="font-weight: 400; font-size:13px;">
                <a href="<?php echo BASE_URL; ?>ingest.php" class="pointerlink">
                    <i class="arrow left"></i>&nbsp; Visit the Data Ingest Station
                </a>
            </small>
        </div>

        <div id="messagedisplaysection">
            <!-- Add an element to display the response -->
            <p id="response"></p>
        </div>

        <div id="userinput">
            <input type="text" name="message" id="messages" placeholder="Type your message here..." />
            <button id="send" class="btn btn-primary"><i class="fa fa-send"></i> Send</button>
        </div>
    </div>

    <!-- Include the jQuery library -->
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
            var safeMessage = $('<div>').text(userMessage).html();
            $("#messagedisplaysection").append(
                `<div class="chat usermessages"><div class="user-avatar"></div>${safeMessage}</div>`
            ).scrollTop($("#messagedisplaysection")[0].scrollHeight);

            $.post("bot.php", {
                messageValue: userMessage
            }, function(response) {
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
