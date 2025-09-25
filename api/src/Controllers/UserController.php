<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Response;

class UserController {
    private UserService $svc;

    public function __construct() {
        $this->svc = new UserService();
    }

    public function register(array $data): void {
        $mail = $data['mail'] ?? null;
        $pass = $data['password'] ?? null;
        if (!$mail || !$pass) {
            Response::json(['error' => 'mail_and_password_required'], 400);
            return;
        }
        $res = $this->svc->register($mail, $pass);
        if (!$res['ok']) {
            Response::json(['error' => $res['error']], 400);
            return;
        }
        Response::json(['user' => $res['user']], 201);
    }

    public function login(array $data): void {
        $mail = $data['mail'] ?? null;
        $pass = $data['password'] ?? null;
        if (!$mail || !$pass) {
            Response::json(['error' => 'mail_and_password_required'], 400);
            return;
        }
        $res = $this->svc->login($mail, $pass);
        if (!$res['ok']) {
            Response::json(['error' => $res['error']], 401);
            return;
        }
        Response::json(['user' => $res['user']], 200);
    }

    public function listUsers(): void {
        Response::json($this->svc->getAllUsers());
    }

    public function getUser(int $id): void {
        $user = $this->svc->getUser($id);
        if (!$user) {
            Response::json(['error' => 'not_found'], 404);
            return;
        }
        Response::json($user);
    }

    public function updateUser(int $id, array $data): void {
        Response::json(['ok' => $this->svc->updateUser($id, $data)]);
    }

    public function deleteUser(int $id): void {
        Response::json(['ok' => $this->svc->deleteUser($id)]);
    }
}
