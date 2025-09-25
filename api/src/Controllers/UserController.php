<?php

namespace App\Controllers;
use App\Services\UserService;
use Exception;

class UserController
{
    private $service;

    public function __construct(UserService $service)
    {
        $this->service = $service;
    }

    /**
     * Handle user registration.
     */
    public function register(array $request): array
    {
        $body = $request['body'] ?? [];
        $email = $body['mail'] ?? '';
        $password = $body['password'] ?? '';
        $isAdmin = false;
        $createdAt = new \DateTime();
        $roleId = 1;

        if (!$email || !$password) {
            return [
                'status' => 400,
                'data' => ['error' => 'Missing required fields']
            ];
        }

        try {
            $id = $this->service->registerUser($email, $password, $isAdmin, $createdAt, $roleId);
            return [
                'status' => 201,
                'data' => ['id' => $id]
            ];
        } catch (Exception $e) {
            return [
                'status' => 409,
                'data' => ['error' => "Unexpected error occurred"]
            ];
        }
    }

    public function getUser(string $userId): array
    {
        if (!$userId) {
            return [
                'status' => 400,
                'data' => ['error' => 'User ID is required']
            ];
        }

        $user = $this->service->getUser(userId: (int) $userId);
        if (!$user) {
            return [
                'status' => 404,
                'data' => ['error' => 'User not found']
            ];
        }

        $userResponse = [
            'id' => $user->getId(),
            'mail' => $user->getMail(),
            'isActive' => $user->isActive(),
            'createdAt' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            'updatedAt' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            'roleId' => $user->getRoleId()
        ];

        return [
            'status' => 200,
            'data' => $userResponse
        ];
    }
}
