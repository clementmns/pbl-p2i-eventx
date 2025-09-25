<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Response;

class UserController
{
    private UserService $svc;

    public function __construct()
    {
        $this->svc = new UserService();
    }

    /**
     * Registers a new user.
     *
     * @param array $data User data containing 'mail' and 'password'.
     * @return void
     */
    public function register(array $data): void
    {
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

    /**
     * Logs in a user.
     *
     * @param array $data User credentials containing 'mail' and 'password'.
     * @return void
     */
    public function login(array $data): void
    {
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

    /**
     * Lists all users.
     *
     * @return void
     */
    public function listUsers(): void
    {
        Response::json($this->svc->getAllUsers());
    }

    /**
     * Gets a user by ID.
     *
     * @param int $id User ID.
     * @return void
     */
    public function getUser(int $id): void
    {
        $user = $this->svc->getUser($id);
        if (!$user) {
            Response::json(['error' => 'not_found'], 404);
            return;
        }
        Response::json($user);
    }

    /**
     * Updates a user by ID.
     *
     * @param int $id User ID.
     * @param array $data Data to update.
     * @return void
     */
    public function updateUser(int $id, array $data): void
    {
        Response::json(['ok' => $this->svc->updateUser($id, $data)]);
    }

    /**
     * Deletes a user by ID.
     *
     * @param int $id User ID.
     * @return void
     */
    public function deleteUser(int $id): void
    {
        Response::json(['ok' => $this->svc->deleteUser($id)]);
    }
}
