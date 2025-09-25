<?php
namespace App\Models\Entity;

class Profile {
    public int $id;
    public ?string $firstName;
    public ?string $lastName;
    public ?string $pictures;
    public ?string $description;
    public int $userId;

    public function __construct(
        int $id,
        ?string $firstName,
        ?string $lastName,
        ?string $pictures,
        ?string $description,
        int $userId
    ) {
        $this->id = $id;
        $this->firstName = $firstName;
        $this->lastName = $lastName;
        $this->pictures = $pictures;
        $this->description = $description;
        $this->userId = $userId;
    }

    public function toArray(): array {
        return [
            'id' => $this->id,
            'firstName' => $this->firstName,
            'lastName' => $this->lastName,
            'pictures' => $this->pictures,
            'description' => $this->description,
            'userId' => $this->userId
        ];
    }
}
