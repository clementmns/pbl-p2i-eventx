<?php
namespace App\Models\Repository;

use App\Models\Database;
use App\Models\Entity\Profile;
use PDO;

class ProfileRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findById(int $id): ?Profile {
        $stmt = $this->db->prepare("SELECT * FROM profiles WHERE id = ?");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) return null;
        return new Profile((int)$r['id'], $r['firstName'], $r['lastName'], $r['pictures'], $r['description'], (int)$r['userId']);
    }

    public function findByUserId(int $userId): ?Profile {
        $stmt = $this->db->prepare("SELECT * FROM profiles WHERE userId = ?");
        $stmt->execute([$userId]);
        $r = $stmt->fetch();
        if (!$r) return null;
        return new Profile((int)$r['id'], $r['firstName'], $r['lastName'], $r['pictures'], $r['description'], (int)$r['userId']);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO profiles (firstName, lastName, pictures, description, userId) VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['firstName'] ?? null,
            $data['lastName'] ?? null,
            $data['pictures'] ?? null,
            $data['description'] ?? null,
            $data['userId']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = []; $params = [];
        if (array_key_exists('firstName', $data)) { $fields[] = "firstName = ?"; $params[] = $data['firstName']; }
        if (array_key_exists('lastName', $data)) { $fields[] = "lastName = ?"; $params[] = $data['lastName']; }
        if (array_key_exists('pictures', $data)) { $fields[] = "pictures = ?"; $params[] = $data['pictures']; }
        if (array_key_exists('description', $data)) { $fields[] = "description = ?"; $params[] = $data['description']; }
        if (empty($fields)) return false;
        $params[] = $id;
        $stmt = $this->db->prepare("UPDATE profiles SET ".implode(',', $fields)." WHERE id = ?");
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        return $this->db->prepare("DELETE FROM profiles WHERE id = ?")->execute([$id]);
    }
}
