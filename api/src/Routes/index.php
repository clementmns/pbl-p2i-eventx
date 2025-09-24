<?php

header('Content-Type: application/json');

require_once __DIR__ . '/Router.php';

use App\Routes\Router;

$router = new Router();

$router->get('/status', 'StatusController', 'getStatus');

$router->dispatch($_SERVER['REQUEST_URI'], $_SERVER['REQUEST_METHOD']);
