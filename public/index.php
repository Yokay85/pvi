<?php
session_start();

require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../config/config.php';

$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.css$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        header('Content-Type: text/css');
        readfile($filePath);
        exit;
    }
}

$controller = new Controller();
$controller->handleRequest();