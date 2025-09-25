<?php

namespace App\Models\Entity;
use DateTime;

class RegistrationModel {
    private int $eventId;
    private int $userId;
    private DateTime $createdAt;
    private ?DateTime $updatedAt;

}
