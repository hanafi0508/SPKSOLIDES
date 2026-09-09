<?php

$appEnv = getenv('APP_ENV') ?: 'dev';

if ($appEnv === 'prod') {
    ini_set('display_errors', '0');
    ini_set('log_errors', '1');
    error_reporting(E_ALL);
}

header('X-Frame-Options: DENY');
header('X-Content-Type-Options: nosniff');
header('Referrer-Policy: strict-origin-when-cross-origin');

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';

$documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$projectRoot = realpath(__DIR__ . '/..');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

if ($documentRoot && $projectRoot && strpos($projectRoot, $documentRoot) === 0) {
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $projectRoot = str_replace('\\', '/', $projectRoot);

    $basePath = rtrim(str_replace($documentRoot, '', $projectRoot), '/');
} else {
    $scriptPath = str_replace('\\', '/', $scriptName);

    $basePath = preg_replace('#/(auth|pages|layouts|functions|config)(/.*)?$#', '', $scriptPath);
    $basePath = rtrim($basePath, '/');
}

define('BASE_URL', $scheme . '://' . $host . $basePath);