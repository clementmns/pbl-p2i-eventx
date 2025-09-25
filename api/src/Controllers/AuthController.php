<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Response;

class AuthController {
    private UserService $service;

    public function __construct() {
        $this->service = new UserService();
    }

    /**
     * @throws \JsonException
     */
    public function register(array $data): void {
        $mail = $data['mail'] ?? null;
        $password = $data['password'] ?? null;

        if (!$mail || !$password) {
            Response::json(['error' => 'mail_and_password_required'], 400);
            return;
        }

        $result = $this->service->register($mail, $password);

        if (!$result['ok']) {
            Response::json(['error' => $result['error']], 400);
            return;
        }

        Response::json([
            'message' => 'User registered successfully',
            'userId'  => $result['userId']
        ], 201);
    }

    /**
     * @throws \JsonException
     */
    public function login(array $data): void {
        $mail = $data['mail'] ?? null;
        $password = $data['password'] ?? null;

        if (!$mail || !$password) {
            Response::json(['error' => 'mail_and_password_required'], 400);
            return;
        }

        $result = $this->service->login($mail, $password);

        if (!$result['ok']) {
            Response::json(['error' => $result['error']], 401);
            return;
        }

        Response::json([
            'message' => 'Login successful',
            'userId'  => $result['userId']
        ], 200);
    }
}
