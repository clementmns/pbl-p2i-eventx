<?php

header('Content-Type: application/json');

$routes = [
    '/status' => ['controller' => 'StatusController', 'action' => 'getStatus'],
];

$request = strtok($_SERVER['REQUEST_URI'], '?');

if (array_key_exists($request, $routes)) {
    $controllerName = $routes[$request]['controller'];
    $actionName = $routes[$request]['action'];

    require_once __DIR__ . '/../Controllers/' . $controllerName . '.php';

    $controller = new $controllerName();
    $response = $controller->$actionName();

    echo json_encode($response);
} else {
    http_response_code(404);
    echo json_encode(['error' => 'Route not found']);
}
