<?php
// Handle static files from public folder
$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$uri = urldecode($uri);

// Remove base path
$uri = str_replace('/iot-tkhs/', '', $uri);

// Check if file exists in public folder
$publicFile = __DIR__ . '/public/' . $uri;

if ($uri !== '/' && file_exists($publicFile) && is_file($publicFile)) {
    // Get file extension
    $ext = pathinfo($publicFile, PATHINFO_EXTENSION);
    
    // Set content type based on extension
    $mimeTypes = [
        'css' => 'text/css',
        'js' => 'application/javascript',
        'json' => 'application/json',
        'png' => 'image/png',
        'jpg' => 'image/jpeg',
        'jpeg' => 'image/jpeg',
        'gif' => 'image/gif',
        'svg' => 'image/svg+xml',
        'ico' => 'image/x-icon',
        'woff' => 'font/woff',
        'woff2' => 'font/woff2',
        'ttf' => 'font/ttf',
        'eot' => 'application/vnd.ms-fontobject'
    ];
    
    $contentType = $mimeTypes[$ext] ?? 'application/octet-stream';
    
    header('Content-Type: ' . $contentType);
    header('Content-Length: ' . filesize($publicFile));
    readfile($publicFile);
    exit;
}

// Forward all other requests to Laravel
$_SERVER['SCRIPT_NAME'] = '/iot-tkhs/index.php';
require __DIR__ . '/public/index.php';