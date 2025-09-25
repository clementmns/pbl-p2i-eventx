<?php
namespace App\Models\Repository;

use App\Models\Database;
use App\Models\Entity\Event;
use PDO;

class EventRepository {
    private PDO $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findAll(): array {
        $stmt = $this->db->query("
        SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.idEvent = e.id) AS registeredCount,
               (SELECT COUNT(*) FROM wishlists w WHERE w.idEvent = e.id) AS wishlistCount
        FROM events e
        ORDER BY e.startDate ASC
    ");
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Event((int)$r['id'], $r['name'], $r['description'], $r['startDate'], $r['endDate'], $r['place'], (int)$r['userId'], (int)$r['registeredCount'], (int)$r['wishlistCount']), $rows);
    }

    public function findById(int $id): ?Event {
        $stmt = $this->db->prepare("
        SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.idEvent = e.id) AS registeredCount,
               (SELECT COUNT(*) FROM wishlists w WHERE w.idEvent = e.id) AS wishlistCount
        FROM events e
        WHERE e.id = ?
    ");
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        if (!$r) return null;
        return new Event((int)$r['id'], $r['name'], $r['description'], $r['startDate'], $r['endDate'], $r['place'], (int)$r['userId'], (int)$r['registeredCount'], (int)$r['wishlistCount']);
    }

    public function findJoinedByUser(int $userId): array {
        $stmt = $this->db->prepare("
        SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.idEvent = e.id) AS registeredCount,
               (SELECT COUNT(*) FROM wishlists w WHERE w.idEvent = e.id) AS wishlistCount
        FROM events e
        INNER JOIN registrations r ON e.id = r.idEvent
        WHERE r.idUser = ?
        ORDER BY e.startDate ASC
    ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Event(
            (int)$r['id'],
            $r['name'],
            $r['description'],
            $r['startDate'],
            $r['endDate'],
            $r['place'],
            (int)$r['userId'],
            (int)$r['registeredCount'],
            (int)$r['wishlistCount']
        ), $rows);
    }

    public function create(array $data): int {
        $stmt = $this->db->prepare(
            "INSERT INTO events (name, description, startDate, endDate, place, userId) VALUES (?, ?, ?, ?, ?, ?)"
        );
        $stmt->execute([
            $data['name'],
            $data['description'] ?? null,
            $data['startDate'],
            $data['endDate'],
            $data['place'],
            $data['userId']
        ]);
        return (int)$this->db->lastInsertId();
    }

    public function update(int $id, array $data): bool {
        $fields = []; $params = [];
        if (isset($data['name'])) { $fields[] = 'name = ?'; $params[] = $data['name']; }
        if (array_key_exists('description',$data)) { $fields[] = 'description = ?'; $params[] = $data['description']; }
        if (isset($data['startDate'])) { $fields[] = 'startDate = ?'; $params[] = $data['startDate']; }
        if (isset($data['endDate'])) { $fields[] = 'endDate = ?'; $params[] = $data['endDate']; }
        if (isset($data['place'])) { $fields[] = 'place = ?'; $params[] = $data['place']; }
        if (isset($data['userId'])) { $fields[] = 'userId = ?'; $params[] = $data['userId']; }
        if (empty($fields)) return false;
        $params[] = $id;
        $sql = "UPDATE events SET ".implode(',',$fields)." WHERE id = ?";
        $stmt = $this->db->prepare($sql);
        return $stmt->execute($params);
    }

    public function delete(int $id): bool {
        $stmt = $this->db->prepare("DELETE FROM events WHERE id = ?");
        return $stmt->execute([$id]);
    }

    // Registration / wishlist helpers
    public function joinEvent(int $eventId, int $userId): bool {
        $this->db->beginTransaction();
        try {
            $stmt = $this->db->prepare("INSERT INTO registrations (idUser,idEvent) VALUES (?,?)");
            $stmt->execute([$userId,$eventId]);
            $this->db->commit();
            return true;
        } catch (\Throwable $e) {
            $this->db->rollBack();
            throw $e;
        }
    }

    public function quitEvent(int $eventId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM registrations WHERE idUser = ? AND idEvent = ?");
        return $stmt->execute([$userId,$eventId]);
    }

    public function addWishlist(int $eventId, int $userId): bool {
        $stmt = $this->db->prepare("INSERT INTO wishlists (idUser,idEvent) VALUES (?,?)");
        return $stmt->execute([$userId,$eventId]);
    }

    public function removeWishlist(int $eventId, int $userId): bool {
        $stmt = $this->db->prepare("DELETE FROM wishlists WHERE idUser = ? AND idEvent = ?");
        return $stmt->execute([$userId,$eventId]);
    }

    public function findWishlistByUser(int $userId): array {
        $stmt = $this->db->prepare("
        SELECT e.*,
               (SELECT COUNT(*) FROM registrations r WHERE r.idEvent = e.id) AS registeredCount,
               (SELECT COUNT(*) FROM wishlists w2 WHERE w2.idEvent = e.id) AS wishlistCount
        FROM events e
        INNER JOIN wishlists w ON e.id = w.idEvent
        WHERE w.idUser = ?
        ORDER BY e.startDate ASC
    ");
        $stmt->execute([$userId]);
        $rows = $stmt->fetchAll(PDO::FETCH_ASSOC);
        return array_map(fn($r) => new Event((int)$r['id'], $r['name'], $r['description'], $r['startDate'], $r['endDate'], $r['place'], (int)$r['userId'], (int)$r['registeredCount'], (int)$r['wishlistCount']), $rows);
    }

}
