<?php
require_once __DIR__ . '/../vendor/autoload.php';

use FrontEnd\Router;
use Controllers\HomeController;
use Controllers\AuthController;
use Controllers\SettingsController;

$router = new Router();

$router->get('/', function (): void {
    $controller = new HomeController();
    $controller->index();
});

// ---------------
// AUTH ROUTES
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
$router->get('/events', function (): void {
    $controller = new Controllers\EventController();
    $controller->eventView();
});
$router->get('/events/create', function (): void {
    $controller = new Controllers\EventController();
    $controller->createEventView();
});

$router->post('/events', function (): void {
    $controller = new Controllers\EventController();
    $controller->createEvent();
});

$router->dispatch();
