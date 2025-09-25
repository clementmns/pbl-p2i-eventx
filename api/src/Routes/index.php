<?php

namespace App\Routes;
use App\Controllers\StatusController;

$db = require __DIR__ . '/../../config/database.php';

header('Content-Type: application/json');

$url = $_SERVER['REQUEST_URI'];
$method = $_SERVER['REQUEST_METHOD'];

switch ($url) {
    case '/api/status':
        $controller = new StatusController();
        if ($method === 'GET') {
            $response = $controller->getStatus();
            echo json_encode($response);
            exit;
        }
        break;
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Not Found']);
        exit;
}
