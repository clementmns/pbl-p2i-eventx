<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Response;

class AuthController
{
    private UserService $service;

    public function __construct()
    {
        $this->service = new UserService();
    }


    /**
     * Register a new user.
     * @param array $data Must contain 'mail' and 'password'.
     * @return void
     */
    public function register(array $data): void
    {
        $mail = $data['mail'] ?? null;
        $password = $data['password'] ?? null;

        if (!$mail || !$password) {
            Response::json(['error' => 'mail_and_password_required'], 400);
            return;
        }

        if (strlen($password) < 8 || !preg_match('/[A-Z]/', $password) || !preg_match('/[a-z]/', $password) || !preg_match('/[0-9]/', $password)) {
            Response::json(['error' => 'weak_password'], 400);
            return;
        }

        $result = $this->service->register($mail, $password);

        if (!$result['ok']) {
            Response::json(['error' => $result['error']], 400);
            return;
        }

        Response::json([
            'message' => 'User registered successfully',
            'user' => $result['user']
        ], 201);
    }

    /**
     * Login a user.
     * @param array $data Must contain 'mail' and 'password'.
     * @return void
     * @throws \JsonException
     */
    public function login(array $data): void
    {
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
            'user' => $result['user']
        ], 200);
    }
}
