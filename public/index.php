<?php
// public/index.php

session_start(); // 👈 NUEVO: para usar mensajes flash

date_default_timezone_set('America/Bogota');

// Definir BASE_URL automáticamente según la ruta del script
$scriptName = $_SERVER['SCRIPT_NAME'] ?? '';
$basePath   = rtrim(str_replace('\\', '/', dirname($scriptName)), '/');

if ($basePath === '' || $basePath === '/') {
    define('BASE_URL', '/');
} else {
    define('BASE_URL', $basePath . '/');
}


// Autocarga muy básica
spl_autoload_register(function ($class) {
    $paths = [
        __DIR__ . '/../app/controllers/' . $class . '.php',
        __DIR__ . '/../app/models/' . $class . '.php',
        __DIR__ . '/../core/' . $class . '.php',
        __DIR__ . '/../config/' . $class . '.php',
    ];

    foreach ($paths as $file) {
        if (file_exists($file)) {
            require_once $file;
            return;
        }
    }
});

$controllerName = $_GET['controller'] ?? 'projects';
$actionName     = $_GET['action'] ?? 'index';

$controllerClass = ucfirst($controllerName) . 'Controller';

if (!class_exists($controllerClass)) {
    die("Controlador no encontrado: $controllerClass");
}

$controller = new $controllerClass();

if (!method_exists($controller, $actionName)) {
    die("Acción no encontrada: $actionName");
}

try {
    $controller->$actionName();
} catch (Exception $e) {
    echo "<h1>Error</h1>";
    echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
}