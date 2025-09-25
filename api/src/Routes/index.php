<?php

namespace App\Routes;
use App\Controllers\StatusController;
use App\Controllers\UserController;
use JsonException;

require_once __DIR__ . '/../../vendor/autoload.php';

$db = require __DIR__ . '/../../config/database.php';


header('Content-Type: application/json');


$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$uri = preg_replace('#^/api#', '', $uri);

/**
 * @throws JsonException
 */
function getJsonBody(): array {
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    return is_array($data) ? $data : [];
}

if($uri === '/status' && $method === 'GET') {
    $controller = new StatusController();
    echo json_encode($controller->getStatus(), JSON_THROW_ON_ERROR);
    exit;
}

if ($uri === '/users' && $method === 'GET') {
    (new UserController())->listUsers();
    exit;
}