<?php

namespace App\Routes;

class Router {
    private $routes = [];

    public function get(string $path, string $controller, string $action): void {
        $this->addRoute('GET', $path, $controller, $action);
    }

    public function post(string $path, string $controller, string $action): void {
        $this->addRoute('POST', $path, $controller, $action);
    }

    public function put(string $path, string $controller, string $action): void {
        $this->addRoute('PUT', $path, $controller, $action);
    }

    public function delete(string $path, string $controller, string $action): void {
        $this->addRoute('DELETE', $path, $controller, $action);
    }

    private function addRoute(string $method, string $path, string $controller, string $action): void {
        $this->routes[$method][$path] = ['controller' => $controller, 'action' => $action];
    }

    public function dispatch(string $requestUri, string $requestMethod): void {
        $path = strtok($requestUri, '?');

        if (isset($this->routes[$requestMethod][$path])) {
            $route = $this->routes[$requestMethod][$path];
            $controllerName = $route['controller'];
            $actionName = $route['action'];

            $controllerClass = "App\\Controllers\\" . $controllerName;
            $controller = new $controllerClass();

            $response = $controller->$actionName();
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Route not found']);
        }
    }
}
