<?php
require __DIR__ . '/vendor/autoload.php'; // Ensure this path is correct for your project structure

// Load environment variables from a .env file if available
if (file_exists(__DIR__ . '/.env')) {
    $dotenv = Dotenv\Dotenv::createImmutable(__DIR__);
    $dotenv->load();
}

// Database host
define('DB_HOST', getenv('DB_HOST') ?: 'localhost');

// Database username
define('DB_USER', getenv('DB_USER') ?: 'root');

// Database password
define('DB_PASS', getenv('DB_PASS') ?: '');

// Database name
define('DB_NAME', getenv('DB_NAME') ?: 'phpbot');
