<?php

namespace App\DataAccess;
use App\DataAccess\BaseDataAccess;
use App\Models\UserModel;
use PDO;

class UserDataAccess extends BaseDataAccess
{
    public function createUser(UserModel $user): bool
    {
        $sql = "INSERT INTO users (mail, password, isAdmin, created_at, update_at, roleId) VALUES (:mail, :password, :isAdmin, :created_at, :update_at, :roleId)";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute([
            ':mail' => $user->getMail(),
            ':password' => $user->getPassword(),
            ':isActive' => $user->isActive(),
            ':created_at' => $user->getCreatedAt()->format('Y-m-d H:i:s'),
            ':update_at' => $user->getUpdatedAt() ? $user->getUpdatedAt()->format('Y-m-d H:i:s') : null,
            ':roleId' => $user->getRoleId()
        ]);
    }

    public function getUserById(int $userId): ?UserModel
    {
        $sql = "SELECT * FROM users WHERE id = :id";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':id' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $this->arrayToUserModel($user) : null;
    }

    public function getUserByEmail(string $mail): ?UserModel
    {
        $sql = "SELECT * FROM users WHERE mail = :mail";
        $stmt = $this->db->prepare($sql);
        $stmt->execute([':mail' => $mail]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        return $user ? $this->arrayToUserModel($user) : null;
    }

    private function arrayToUserModel(array $data): UserModel
    {
        $user = new UserModel();
        $user->setId((int) $data['id']);
        $user->setMail($data['mail']);
        $user->setPassword($data['password']);
        $user->setIsActive((bool) $data['isActive']);
        $user->setCreatedAt(new \DateTime($data['created_at']));
        $user->setUpdatedAt(isset($data['update_at']) ? new \DateTime($data['update_at']) : null);
        $user->setRoleId((int) $data['roleId']);
        return $user;
    }
}


