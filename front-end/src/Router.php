<?php
namespace FrontEnd;

class Router
{
    private $routes = [];
    private $routeMeta = [];

    public function get($path, $handler, $public = false)
    {
        $this->routes['GET'][$path] = $handler;
        $this->routeMeta['GET'][$path] = ['public' => $public];
    }

    public function post($path, $handler, $public = false)
    {
        $this->routes['POST'][$path] = $handler;
        $this->routeMeta['POST'][$path] = ['public' => $public];
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        require_once __DIR__ . '/Services/SessionManager.php';
        $sessionManager = new \Services\SessionManager();
        $sessionManager->start();
        $meta = $this->routeMeta[$method][$uri] ?? [];
        $isPublic = $meta['public'] ?? false;
        if (!$isPublic && !$sessionManager->isAuthenticated()) {
            header('Location: /login');
            exit;
        }
        $handler = $this->routes[$method][$uri] ?? null;
        if ($handler) {
            call_user_func($handler);
        } else {
            $controller = new \Controllers\ErrorController();
            $controller->notFound();
        }
    }
}
