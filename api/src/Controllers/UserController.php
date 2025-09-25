<?php
namespace App\Controllers;

use App\Services\UserService;
use App\Utils\Auth;
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
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
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
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
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
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        Response::json(['ok' => $this->svc->updateUser($id, $data)]);
    }

    /**
     * Lists all users - Admin only endpoint.
     *
     * @return void
     */
    public function listAllUsers(): void
    {
        // Check if user is admin
        if (!Auth::isAdmin()) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $users = $this->svc->getAllUsers();
        Response::json(['users' => $users]);
    }

    /**
     * Deletes a user by ID.
     *
     * @param int $id User ID.
     * @return void
     */
    public function deleteUser(int $id): void
    {
        // Check if user is authenticated and is admin
        if (!Auth::isAdmin()) {
            Response::json(['error' => 'Forbidden: Admin access required'], 403);
            return;
        }

        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        Response::json(['ok' => $this->svc->deleteUser($id)]);
    }
}
