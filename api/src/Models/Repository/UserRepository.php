<?php
namespace App\Models\Repository;

use App\Models\Database;
use App\Models\Entity\User;
use PDO;

class UserRepository
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE id = ?");
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row)
            return null;
        $user = new User((int) $row['id'], $row['mail'], $row['password'], (bool) $row['isActive'], (int) $row['roleId']);
        $profileStmt = $this->db->prepare("SELECT * FROM profiles WHERE userId = ?");
        $profileStmt->execute([$user->id]);
        $profileRow = $profileStmt->fetch();
        if ($profileRow) {
            $user->profile = $profileRow;
        }
        return $user;
    }

    public function findByMail(string $mail): ?User
    {
        $stmt = $this->db->prepare("SELECT * FROM users WHERE mail = ?");
        $stmt->execute([$mail]);
        $row = $stmt->fetch();
        if (!$row)
            return null;
        $user = new User((int) $row['id'], mail: $row['mail'], passwordHash: $row['password'], isActive: (bool) $row['isActive'], roleId: (int) $row['roleId']);
        $profileStmt = $this->db->prepare("SELECT * FROM profiles WHERE userId = ?");
        $profileStmt->execute([$user->id]);
        $profileRow = $profileStmt->fetch();
        if ($profileRow) {
            $user->profile = $profileRow;
        }
        return $user;
    }

    public function create(string $mail, string $passwordHash, int $roleId = 2): int
    {
        $roleStmt = $this->db->prepare("SELECT id FROM roles WHERE id = ?");
        $roleStmt->execute([$roleId]);
        if (!$roleStmt->fetch()) {
            throw new \InvalidArgumentException("Role ID $roleId does not exist.");
        }
        $stmt = $this->db->prepare("INSERT INTO users (mail, password, isActive, roleId) VALUES (?, ?, 1, ?)");
        $stmt->execute([$mail, $passwordHash, $roleId]);
        return (int) $this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool
    {
        $fields = [];
        $params = [];
        if (isset($data['mail'])) {
            $fields[] = 'mail = ?';
            $params[] = $data['mail'];
        }
        if (isset($data['password'])) {
            $fields[] = 'password = ?';
            $params[] = password_hash($data['password'], PASSWORD_BCRYPT);
        }
        if (isset($data['isActive'])) {
            $fields[] = 'isActive = ?';
            $params[] = (int) $data['isActive'];
        }
        if (isset($data['roleId'])) {
            $fields[] = 'roleId = ?';
            $params[] = $data['roleId'];
        }
        if (empty($fields))
            return false;
        $params[] = $id;
        $sql = "UPDATE users SET " . implode(',', $fields) . " WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM users WHERE id = ?");
        return $stmt->execute([$id]);
    }

    public function findAll(): array
    {
        $stmt = $this->db->query("SELECT id, mail, isActive, roleId FROM users");
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
