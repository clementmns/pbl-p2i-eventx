<?php
namespace App\Controllers;

use App\Services\ProfileService;
use App\Services\UserService;
use App\Utils\Auth;
use App\Utils\Response;
use JsonException;

class ProfileController
{
    private ProfileService $service;
    private UserService $userService;

    public function __construct()
    {
        $this->service = new ProfileService();
        $this->userService = new UserService();
    }

    /**
     * Get a profile by user ID.
     * @param int $userId User ID
     * @return void
     */
    public function getProfileByUser(int $userId)
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        Response::json($this->service->getProfileByUser($userId));
    }

    /**
     * Create or update a profile for a user.
     * @param int $userId User ID
     * @param array $data Profile data
     * @return void
     */
    public function upsertProfile(int $userId, array $data)
    {
        $payload = Auth::getBearerTokenPayload();
        if (!$payload) {
            Response::json(['error' => 'Unauthorized'], 401);
            return;
        }
        if (!$userId) {
            Response::json(['error' => 'userId_required'], 400);
            return;
        }
        // check if user exists
        if (!$this->userService->getUser($userId)) {
            Response::json(['error' => 'user_not_found'], 404);
            return;
        }
        Response::json($this->service->upsertProfile($userId, $data));
    }
}
