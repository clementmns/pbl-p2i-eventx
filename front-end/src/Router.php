<?php
namespace FrontEnd;

class Router
{
    private $routes = [];

    public function get($path, $handler)
    {
        $this->routes['GET'][$path] = $handler;
    }

    public function post($path, $handler)
    {
        $this->routes['POST'][$path] = $handler;
    }

    public function dispatch()
    {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $handler = $this->routes[$method][$uri] ?? null;
        if ($handler) {
            call_user_func($handler);
        } else {
            $controller = new \Controllers\ErrorController();
            $controller->notFound();
        }
    }
}
