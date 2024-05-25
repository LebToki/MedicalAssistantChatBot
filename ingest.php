<?php
//	error_reporting(E_ALL);
//	ini_set('display_errors', 1);
//
//	echo 'Debug: Starting script execution.<br>';
	
	require 'database_config.php';
	
	try {
		//echo 'Debug: Connecting to database.<br>';
		// Create database connection using PDO
		$conn = new PDO("mysql:host=" . DB_HOST . ";dbname=" . DB_NAME, DB_USER, DB_PASS);
		$conn->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
		//echo 'Debug: Connected to database.<br>';
	} catch (PDOException $e) {
		die('Connection failed: ' . htmlspecialchars($e->getMessage(), ENT_QUOTES, 'UTF-8'));
	}
	
	if ($_SERVER['REQUEST_METHOD'] === 'POST') {
		echo 'Debug: Processing POST request.<br>';
		if (isset($_FILES['jsonFiles']) && count($_FILES['jsonFiles']['name']) > 0) {
			echo 'Debug: Files detected.<br>';
			$totalFiles = count($_FILES['jsonFiles']['name']);
			$processedFiles = 0;
			$errorFiles = [];
			
			$checkDuplicateStmt = $conn->prepare("SELECT COUNT(*) FROM chatbot WHERE messages = :message AND response = :response");
			$insertStmt = $conn->prepare("INSERT INTO chatbot (messages, response) VALUES (:message, :response)");
			
			for ($i = 0; $i < $totalFiles; $i++) {
				$fileName = $_FILES['jsonFiles']['tmp_name'][$i];
				echo 'Debug: Processing file: ' . $_FILES['jsonFiles']['name'][$i] . '<br>';
				$data = json_decode(file_get_contents($fileName), true);
				
				if (json_last_error() !== JSON_ERROR_NONE) {
					echo 'Debug: JSON error in file: ' . $_FILES['jsonFiles']['name'][$i] . '<br>';
					$errorFiles[] = $_FILES['jsonFiles']['name'][$i];
					continue;
				}
				
				foreach ($data as $item) {
					if (isset($item['input'], $item['output'])) {
						$message = $item['input'];
						$response = $item['output'];
						$checkDuplicateStmt->bindParam(':message', $message);
						$checkDuplicateStmt->bindParam(':response', $response);
						$checkDuplicateStmt->execute();
						$count = $checkDuplicateStmt->fetchColumn();
						
						if ($count == 0) {
							$insertStmt->bindParam(':message', $message);
							$insertStmt->bindParam(':response', $response);
							if (!$insertStmt->execute()) {
								echo 'Debug: Insert failed for message: ' . $message . '<br>';
								$errorFiles[] = $_FILES['jsonFiles']['name'][$i];
							}
						}
					}
				}
				$processedFiles++;
			}
			// Return a response to the client
			echo json_encode([
				'processedFiles' => $processedFiles,
				'totalFiles' => $totalFiles,
				'errorFiles' => $errorFiles
			]);
			exit;
		}
	}
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
          font-family: -apple-system,system-ui,"Segoe UI",Helvetica,Arial,sans-serif,"Apple Color Emoji","Segoe UI Emoji";
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

      .pointerlink:hover, .pointerlink:focus {
          filter: brightness(90%);
          outline: none; /* Only remove outlines if replacing with another focus indicator */
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
          background: #fff;
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

      #file-drop-area {
          height: 50px;
          width: 90%;
          position: absolute;
          left: 5%;
          bottom: 10%;
          border: 2px dashed #aaa;
          border-radius: 10px;
          display: flex;
          align-items: center;
          justify-content: center;
          cursor: pointer;
      }

      #file-drop-area:hover {
          background-color: #f0f0f0;
      }

      #upload-message {
          font-size: 18px;
          color: #333;
      }

      .hidden {
          display: none;
      }

      #upload-spinner {
          display: none;
      }

      .spinner {
          width: 40px;
          height: 40px;
          border: 4px solid rgba(255, 255, 255, 0.3);
          border-radius: 50%;
          border-top-color: #fff;
          animation: spin 1s ease-in-out infinite;
      }

      @keyframes spin {
          to {
              transform: rotate(360deg);
          }
      }

      #progress-bar {
          width: 100%;
          background-color: #f0f0f0;
          border-radius: 5px;
          margin-bottom: 10px;
      }

      #progress-bar-fill {
          height: 20px;
          background-color: #19c37d;
          border-radius: 5px;
          width: 0;
          transition: width 0.5s ease-in-out;
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

          #file-drop-area {
              height: 40px;
              width: 90%;
              bottom: 10px;
          }

          #upload-message {
              font-size: 14px;
          }

          .spinner {
              width: 30px;
              height: 30px;
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

          #file-drop-area {
              height: 40px;
              width: 90%;
              bottom: 10px;
          }

          #upload-message {
              font-size: 14px;
          }

          .spinner {
              width: 30px;
              height: 30px;
          }
      }
	</style>
</head>

<body>
<div id="container">
	<div id="header">
		<img src="assets/ingest.png" alt="Ingest Icon" style="width:40px;">
		<span>Medical Doctor AI Assistant Bot > Ingest Client</span> &nbsp;&nbsp;&nbsp;
		<small style="font-weight: 400; font-size:13px;">
			<a href="index.php" class="pointerlink">
				<i class="arrow left"></i>&nbsp; Back to the AiBot
			</a>
		</small>
	</div>
	
	<div id="screen">
		<div id="file-drop-area">
			<p id="upload-message">Drag & Drop JSON files here or click to upload</p>
			<input type="file" id="file-input" name="jsonFiles[]" multiple class="hidden">
			<div id="upload-spinner" class="spinner"></div>
		</div>
		<div id="progress-bar" class="hidden">
			<div id="progress-bar-fill"></div>
		</div>
		<div id="messagedisplaysection"></div>
	</div>
</div>

<!-- Include the jQuery library -->
<script src="https://ajax.googleapis.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>

<script>
    $(document).ready(function() {
        let fileInput = $('#file-input');
        let dropArea = $('#file-drop-area');
        let uploadMessage = $('#upload-message');
        let uploadSpinner = $('#upload-spinner');
        let progressBar = $('#progress-bar');
        let progressBarFill = $('#progress-bar-fill');
        let messageDisplaySection = $('#messagedisplaysection');

        dropArea.on('click', function() {
            fileInput.click();
        });

        dropArea.on('dragover', function(event) {
            event.preventDefault();
            event.stopPropagation();
            dropArea.css('background-color', '#f0f0f0');
        });

        dropArea.on('dragleave', function(event) {
            event.preventDefault();
            event.stopPropagation();
            dropArea.css('background-color', '#fff');
        });

        dropArea.on('drop', function(event) {
            event.preventDefault();
            event.stopPropagation();
            dropArea.css('background-color', '#fff');
            let files = event.originalEvent.dataTransfer.files;
            handleFiles(files);
        });

        fileInput.on('change', function(event) {
            let files = event.target.files;
            handleFiles(files);
        });

        function handleFiles(files) {
            uploadMessage.addClass('hidden');
            uploadSpinner.show();
            progressBar.removeClass('hidden');
            messageDisplaySection.html('');

            let totalFiles = files.length;
            let processedFiles = 0;

            let formData = new FormData();
            for (let i = 0; i < totalFiles; i++) {
                formData.append('jsonFiles[]', files[i]);
            }

            $.ajax({
                url: 'ingest.php',
                type: 'POST',
                data: formData,
                contentType: false,
                processData: false,
                success: function(response) {
                    console.log('Debug: AJAX request successful.');
                    response = JSON.parse(response);
                    processedFiles = response.processedFiles;
                    totalFiles = response.totalFiles;
                    let progress = (processedFiles / totalFiles) * 100;
                    progressBarFill.css('width', progress + '%');

                    if (progress === 100) {
                        uploadSpinner.hide();
                        progressBar.addClass('hidden');
                        if (response.errorFiles.length > 0) {
                            messageDisplaySection.html('<p>Some files could not be processed:</p><ul class="error-file">' + response.errorFiles.join('<li>') + '</ul>');
                        } else {
                            messageDisplaySection.html('<p>All files have been processed successfully.</p>');
                        }
                    }
                },
                error: function(error) {
                    console.log('Debug: AJAX request failed.');
                    uploadSpinner.hide();
                    progressBar.addClass('hidden');
                    messageDisplaySection.html('<p>An error occurred while processing the files.</p>');
                }
            });
        }
    });
</script>
</body>
</html>
