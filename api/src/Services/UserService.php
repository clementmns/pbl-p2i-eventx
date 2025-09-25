<?php
namespace App\Services;

use App\Models\Repository\UserRepository;

class UserService
{
    private UserRepository $repo;

    public function __construct()
    {
        $this->repo = new UserRepository();
    }

    public function register(string $mail, string $password): array
    {
        if (!filter_var($mail, FILTER_VALIDATE_EMAIL)) {
            return ['ok' => false, 'error' => 'invalid_email'];
        }
        if ($this->repo->findByMail($mail)) {
            return ['ok' => false, 'error' => 'already_exists'];
        }
        $hash = password_hash($password, PASSWORD_BCRYPT);
        $id = $this->repo->create($mail, $hash, 1);
        $user = $this->repo->findById($id);
        $user->passwordHash = '***';
        return ['ok' => true, 'user' => $user];
    }

    public function login(string $mail, string $password): array
    {
        $user = $this->repo->findByMail($mail);
        if (!$user || !password_verify($password, $user->passwordHash)) {
            return ['ok' => false, 'error' => 'invalid_credentials'];
        }
        $user->passwordHash = '***';
        return ['ok' => true, 'user' => $user];
    }

    public function getAllUsers(): array
    {
        return $this->repo->findAll();
    }

    public function getUser(int $id): ?array
    {
        $user = $this->repo->findById($id);
        return $user ? $user->toArray() : null;
    }

    public function updateUser(int $id, array $data): bool
    {
        return $this->repo->update($id, $data);
    }

    public function deleteUser(int $id): bool
    {
        return $this->repo->delete($id);
    }
}
