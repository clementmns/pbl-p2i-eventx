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
});
$router->post('/login', function (): void {
    $controller = new AuthController();
    $controller->login($_POST['mail'] ?? '', $_POST['password'] ?? '');
});

$router->get('/register', function (): void {
    $controller = new AuthController();
    $controller->registerView();
});
$router->post('/register', function (): void {
    $controller = new AuthController();
    $controller->register($_POST['mail'] ?? '', $_POST['password'] ?? '', $_POST['confirm_password'] ?? '');
});

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

$router->dispatch();
