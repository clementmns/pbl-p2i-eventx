<?php
namespace App\Models\Entity;

class Event {
    public ?int $id;
    public string $name;
    public ?string $description;
    public string $startDate;
    public string $endDate;
    public string $place;
    public int $userId;
    public int $registeredCount = 0;
    public int $wishlistCount = 0;

    public function __construct(?int $id, string $name, ?string $description, string $startDate, string $endDate, string $place, int $userId, int $registeredCount = 0, int $wishlistCount = 0) {
        $this->id = $id;
        $this->name = $name;
        $this->description = $description;
        $this->startDate = $startDate;
        $this->endDate = $endDate;
        $this->place = $place;
        $this->userId = $userId;
        $this->registeredCount = $registeredCount;
        $this->wishlistCount = $wishlistCount;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'startDate' => $this->startDate,
            'endDate' => $this->endDate,
            'place' => $this->place,
            'userId' => $this->userId,
            'registeredCount' => $this->registeredCount,
            'wishlistCount' => $this->wishlistCount
        ];
    }
}
