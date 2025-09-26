<?php
require_once __DIR__ . '/../vendor/autoload.php';

date_default_timezone_set('Europe/Paris');

use FrontEnd\Router;
use Controllers\HomeController;
use Controllers\AuthController;
use Controllers\SettingsController;
use Controllers\EventController;

$router = new Router();

$router->get('/', function (): void {
    $controller = new HomeController();
    $controller->index();
});

// ---------------
// AUTH ROUTES
// ---------------
$router->get('/login', function (): void {
    $controller = new AuthController();
    $controller->loginView();
}, true);
$router->post('/login', function (): void {
    $controller = new AuthController();
    $controller->login($_POST['mail'] ?? '', $_POST['password'] ?? '');
}, true);

$router->get('/register', function (): void {
    $controller = new AuthController();
    $controller->registerView();
}, true);
$router->post('/register', function (): void {
    $controller = new AuthController();
    $controller->register($_POST['mail'] ?? '', $_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
}, true);

$router->get('/logout', function (): void {
    $controller = new AuthController();
    $controller->logout();
});

// ---------------
// SETTINGS ROUTES
// ---------------
$router->get('/settings', function (): void {
    $controller = new SettingsController();
    $controller->settingsView();
});
$router->post('/settings', function (): void {
    $controller = new SettingsController();
    $controller->settings();
});

// ---------------
// EVENT ROUTES
// ---------------
$router->get('/events/create', function (): void {
    $controller = new EventController();
    $controller->createEventView();
});

$router->post('/events/create', function (): void {
    $controller = new EventController();
    $controller->createEvent();
});

$router->get('/events/edit', function (): void {
    $controller = new EventController();
    $controller->editEventView();
});

$router->post('/events/edit', function (): void {
    $controller = new EventController();
    $controller->editEvent();
});

$router->post('/events/delete', function (): void {
    $controller = new EventController();
    $controller->deleteEvent();
});

$router->post('/events/join', function (): void {
    $controller = new EventController();
    $controller->joinEvent();
});

$router->post('/events/quit', function (): void {
    $controller = new EventController();
    $controller->quitEvent();
});

$router->post('/wishlist/add', function (): void {
    $controller = new EventController();
    $controller->addToWishlist();
});

$router->post('/wishlist/remove', function (): void {
    $controller = new EventController();
    $controller->removeFromWishlist();
});

$router->dispatch();
