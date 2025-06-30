<?php
// Shared configuration for web entry points
$isSecure = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || (isset($_SERVER['SERVER_PORT']) && $_SERVER['SERVER_PORT'] == 443);
$protocol = $isSecure ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = dirname($_SERVER['SCRIPT_NAME'] ?? '');
$scriptDir = rtrim($scriptDir, '/') . '/';
if (!defined('BASE_URL')) {
    define('BASE_URL', $protocol . $host . $scriptDir);
}

