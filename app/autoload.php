<?php

require_once __DIR__ . '/../config/config.php';

spl_autoload_register(function ($class_name) {
    $dirs = ['controllers', 'models'];
    foreach ($dirs as $dir) {
        $file = __DIR__ . '/' . $dir . '/' . $class_name . '.php';
        if (file_exists($file)) {
            require_once $file;
        }
    }
});