<?php
	require 'database_config.php';
	
	$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
	if ($conn->connect_error) {
		die('Connection failed: ' . $conn->connect_error);
	}
	
	// Assuming `index.php` or your main script resides in the root of your application folder
	define('BASE_URL', (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . dirname($_SERVER['SCRIPT_NAME']) . '/');
	
	
	$directory = __DIR__ . '/training/';
	$files = array_filter(scandir($directory), function ($file) use ($directory) {
		return is_file($directory . $file) && pathinfo($file, PATHINFO_EXTENSION) === 'json';
	});
	
	$checkDuplicateStmt = $conn->prepare("SELECT COUNT(*) FROM chatbot WHERE messages = ? AND response = ?");
	$insertStmt = $conn->prepare("INSERT INTO chatbot (messages, response) VALUES (?, ?)");
	
	$totalFiles = count($files);
	$processedFiles = 0;
	$errorFiles = [];
	
	foreach ($files as $file) {
		$data = json_decode(file_get_contents($directory . $file), true);
		if (json_last_error() !== JSON_ERROR_NONE) {
			$errorFiles[] = $file;
			continue;
		}
		
		foreach ($data as $item) {
			if (isset($item['input'], $item['output'])) {
				$message = $item['input'];
				$response = $item['output'];
				$checkDuplicateStmt->bind_param("ss", $message, $response);
				$checkDuplicateStmt->execute();
				$checkDuplicateStmt->bind_result($count);
				$checkDuplicateStmt->fetch();
				
				if ($count == 0) {
					$insertStmt->bind_param("ss", $message, $response);
					$insertStmt->execute();
				}
			}
		}
		$processedFiles++;
	}
	
	$checkDuplicateStmt->close();
	$insertStmt->close();
	$conn->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
	<meta charset="UTF-8">
	<title>Medical Doctor AI Assistant Bot > Ingest Client</title>
	<link rel="icon" type="image/png" href="<?php echo BASE_URL; ?>assets/aibot.png">
	<link rel="stylesheet" href="<?php echo BASE_URL; ?>css/bot.css">
</head>
<body>
<div id="container">
	<div id="screen">
		<div id="header">
			<img src="<?php echo BASE_URL; ?>assets/ingest.png" alt="Ingest Icon" style="width:40px;">
			&nbsp; Medical Doctor AI Helper Bot &nbsp; <i class="arrow right"></i>&nbsp;  Ingest Station &nbsp;
			<small style="font-weight: 400; font-size:13px;">
				<a href="<?php echo BASE_URL; ?>index.php" class="pointerlink">
					<i class="arrow left"></i>&nbsp;
																		Back to the AiBot
				</a>
			</small>
		</div>
		
		
		<div class="progress-bar">
			<div class="progress-bar-fill" style="width: <?php echo ($processedFiles / $totalFiles) * 100; ?>%;"></div>
		</div>
		
		<h2>Processing Files:</h2>
		<ul>
			<?php foreach ($files as $file) {
				if (is_file($directory . $file)) {
					echo '<li' . (in_array($file, $errorFiles) ? ' class="error-file"' : '') . '>' . $file . '</li>';
				}
			} ?>
		</ul>
		
		<?php if (count($errorFiles) > 0) : ?>
			<h2>Error Files:</h2>
			<ul>
				<?php foreach ($errorFiles as $file) {
					echo '<li>' . $file . '</li>';
				} ?>
			</ul>
		<?php endif; ?>
	</div>
</div> <!-- Add the closing div tag for the container -->

</body>
</html>
