<?php

namespace App\Services;
use App\DataAccess\UserDataAccess;
use App\Models\UserModel;
use Exception;

class UserService
{
    private $dataAccess;

    public function __construct(UserDataAccess $dataAccess)
    {
        $this->dataAccess = $dataAccess;
    }

    /**
     * Register a new user.
     */
    public function registerUser(string $email, string $password, bool $isActive, \DateTime $createdAt, int $roleId): int
    {
        if ($this->dataAccess->getUserByEmail($email)) {
            throw new Exception('User already exists');
        }
        $passwordHash = password_hash($password, PASSWORD_DEFAULT);
        $user = new UserModel();
        $user->setMail($email);
        $user->setPassword($passwordHash);
        $user->setIsActive($isActive);
        $user->setCreatedAt($createdAt);
        $user->setRoleId($roleId);
        return $this->dataAccess->createUser($user);
    }

    /**
     * Retrieve a user by ID.
     */
    public function getUser(int $userId): ?UserModel
    {
        return $this->dataAccess->getUserById($userId);
    }

}
