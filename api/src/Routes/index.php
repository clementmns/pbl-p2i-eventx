<?php

namespace App\Routes;

use App\Controllers\AuthController;
use App\Controllers\ProfileController;
use App\Controllers\StatusController;
use App\Controllers\UserController;
use App\Controllers\EventController;
use App\Utils\Response;
use JsonException;

require_once __DIR__ . '/../../vendor/autoload.php';

header('Content-Type: application/json');

$uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$method = $_SERVER['REQUEST_METHOD'];
$uri = preg_replace('#^/api#', '', $uri);

/**
 * @throws JsonException
 */
function getJsonBody(): array
{
    $raw = file_get_contents('php://input');
    $data = json_decode($raw, true, 512, JSON_THROW_ON_ERROR);
    return is_array($data) ? $data : [];
}

// ---------- Status ----------
if ($uri === '/status' && $method === 'GET') {
    $controller = new StatusController();
    echo json_encode($controller->getStatus(), JSON_THROW_ON_ERROR);
    exit;
}

if ($uri === '/' && $method === 'GET') {
    $controller = new StatusController();
    echo json_encode($controller->getRoutes(), JSON_THROW_ON_ERROR);
    exit;
}

// ---------- Users ----------
if ($uri === '/users' && $method === 'GET') {
    (new UserController())->listUsers();
    exit;
}
if (preg_match('#^/users/(\d+)$#', $uri, $m)) {
    $id = (int) $m[1];
    if ($method === 'GET') {
        (new UserController())->getUser($id);
        exit;
    }
    if ($method === 'PUT') {
        (new UserController())->updateUser($id, getJsonBody());
        exit;
    }
    if ($method === 'DELETE') {
        (new UserController())->deleteUser($id);
        exit;
    }
}

// ---------- Auth ----------
if ($uri === '/auth/register' && $method === 'POST') {
    try {
        (new AuthController())->register(getJsonBody());
    } catch (JsonException $e) {
    }
    exit;
}
if ($uri === '/auth/login' && $method === 'POST') {
    try {
        (new AuthController())->login(getJsonBody());
    } catch (JsonException $e) {
    }
    exit;
}

// ---------- Events ----------
$eventController = new EventController();

if ($uri === '/events' && $method === 'GET') {
    try {
        $eventController->listEvents();
    } catch (JsonException $e) {
    }
    exit;
}
if ($uri === '/events' && $method === 'POST') {
    try {
        $eventController->createEvent(getJsonBody());
    } catch (JsonException $e) {
    }
    exit;
}
if (preg_match('#^/events/(\d+)$#', $uri, $m)) {
    $id = (int) $m[1];
    if ($method === 'GET') {
        $eventController->getEvent($id);
        exit;
    }
    if ($method === 'PUT') {
        try {
            $eventController->updateEvent($id, getJsonBody());
        } catch (JsonException $e) {
        }
        exit;
    }
    if ($method === 'DELETE') {
        $eventController->deleteEvent($id);
        exit;
    }
}

if (preg_match('#^/events/user/(\d+)$#', $uri, $m) && $method === 'GET') {
    try {
        $eventController->getEventsJoinedByUser();
    } catch (JsonException $e) {
    }
    exit;
}

// ---------- Join / Quit ----------
if (preg_match('#^/events/(\d+)/join$#', $uri, $m) && $method === 'POST') {
    try {
        $data = getJsonBody();
    } catch (JsonException $e) {
    }
    try {
        $eventController->joinEvent((int) $m[1]);
    } catch (JsonException $e) {
    }
    exit;
}
if (preg_match('#^/events/(\d+)/quit$#', $uri, $m) && $method === 'POST') {
    try {
        $data = getJsonBody();
    } catch (JsonException $e) {
    }
    try {
        $eventController->quitEvent((int) $m[1]);
    } catch (JsonException $e) {
    }
    exit;
}

// ---------- Wishlist ----------
if (preg_match('#^/events/(\d+)/wishlist/add$#', $uri, $m) && $method === 'POST') {
    try {
        $data = getJsonBody();
    } catch (JsonException $e) {
    }
    try {
        $eventController->addWishlist((int) $m[1]);
    } catch (JsonException $e) {
    }
    exit;
}
if (preg_match('#^/events/(\d+)/wishlist/remove$#', $uri, $m) && $method === 'POST') {
    try {
        $data = getJsonBody();
    } catch (JsonException $e) {
    }
    try {
        $eventController->removeWishlist((int) $m[1]);
    } catch (JsonException $e) {
    }
    exit;
}

// ---------- List Wishlist ----------
if ($uri === '/events/wishlist' && $method === 'GET') {
    $userId = $_GET['userId'] ?? null;
    try {
        $eventController->listWishlist();
    } catch (JsonException $e) {
    }
    exit;
}

// ----------- Profile ----------
if (preg_match('#^/profiles/user/(\d+)$#', $uri, $m)) {
    $userId = (int) $m[1];
    if ($method === 'GET') {
        try {
            (new ProfileController())->getProfileByUser();
        } catch (JsonException $e) {
        }
        exit;
    }
    if ($method === 'PUT') {
        try {
            (new ProfileController())->upsertProfile(getJsonBody());
        } catch (JsonException $e) {
        }
        exit;
    }
}

// ---------- Fallback ----------
try {
    Response::json(['error' => 'Route not found'], 404);
} catch (JsonException $e) {
}
