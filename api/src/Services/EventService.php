<?php
namespace App\Services;

use App\Models\Repository\EventRepository;

class EventService
{
    private EventRepository $repo;

    public function __construct()
    {
        $this->repo = new EventRepository();
    }

    public function getAllEvents(): array
    {
        return array_map(fn($e) => $e->toArray(), $this->repo->findAll());
    }

    public function getEvent(int $id)
    {
        return $this->repo->findById($id)?->toArray();
    }

    public function getEventsJoinedByUser(int $userId): array
    {
        return array_map(fn($e) => $e->toArray(), $this->repo->findJoinedByUser($userId));
    }

    public function createEvent(array $data)
    {
        if (empty($data['name']) || empty($data['startDate']) || empty($data['endDate']) || empty($data['place'])) {
            return ['ok' => false, 'error' => 'invalid_data'];
        }
        $id = $this->repo->create($data);
        return ['ok' => true, 'id' => $id];
    }

    public function updateEvent(int $id, array $data)
    {
        return ['ok' => $this->repo->update($id, $data)];
    }

    public function deleteEvent(int $id)
    {
        return ['ok' => $this->repo->delete($id)];
    }

    public function joinEvent(int $eventId, int $userId)
    {
        try {
            $this->repo->joinEvent($eventId, $userId);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'db_error', 'message' => $e->getMessage()];
        }
    }

    public function quitEvent(int $eventId, int $userId)
    {
        return ['ok' => $this->repo->quitEvent($eventId, $userId)];
    }

    public function getWishlist(int $userId): array
    {
        $events = $this->repo->findWishlistByUser($userId);
        return array_map(fn($e) => $e->toArray(), $events);
    }

    public function addWishlist(int $eventId, int $userId)
    {
        try {
            $this->repo->addWishlist($eventId, $userId);
            return ['ok' => true];
        } catch (\Throwable $e) {
            return ['ok' => false, 'error' => 'db_error', 'message' => $e->getMessage()];
        }
    }

    public function removeWishlist(int $eventId, int $userId)
    {
        return ['ok' => $this->repo->removeWishlist($eventId, $userId)];
    }
}
