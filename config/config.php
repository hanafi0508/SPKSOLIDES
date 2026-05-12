<?php

$scheme = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https' : 'http';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$documentRoot = !empty($_SERVER['DOCUMENT_ROOT']) ? realpath($_SERVER['DOCUMENT_ROOT']) : false;
$projectRoot = realpath(__DIR__ . '/..');
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';

if ($documentRoot && $projectRoot && str_starts_with($projectRoot, $documentRoot)) {
    $documentRoot = str_replace('\\', '/', $documentRoot);
    $projectRoot = str_replace('\\', '/', $projectRoot);
    $basePath = rtrim(str_replace($documentRoot, '', $projectRoot), '/');
} else {
    $scriptPath = str_replace('\\', '/', $scriptName);
    $basePath = preg_replace('#/(auth|pages|layouts|functions|config)(/.*)?$#', '', $scriptPath);
    $basePath = rtrim($basePath, '/');
}

define('BASE_URL', $scheme . '://' . $host . $basePath);
