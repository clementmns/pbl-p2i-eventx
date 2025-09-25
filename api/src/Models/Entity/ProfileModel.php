<?php

namespace App\Models\Entity;
use DateTime;

class ProfileModel {
    private int $id;
    private string $firstName;
    private string $lastName;
    private ?string $pictures;
    private ?string $description;
    private DateTime $createdAt;
    private ?DateTime $updatedAt;
    private int $userId;

}
