<?php
// Налаштування автозавантаження та ініціалізація
require_once __DIR__ . '/../app/autoload.php';
require_once __DIR__ . '/../config/config.php';

// У public/index.php перед обробкою маршрутів
$requestUri = $_SERVER['REQUEST_URI'];
if (preg_match('/\.css$/', $requestUri)) {
    $filePath = __DIR__ . $requestUri;
    if (file_exists($filePath)) {
        header('Content-Type: text/css');
        readfile($filePath);
        exit;
    }
}

// Обробка запитів (маршрутизація)
$controller = new Controller();
$controller->handleRequest();