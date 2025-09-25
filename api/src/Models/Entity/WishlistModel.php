<?php

namespace App\Models\Entity;
use DateTime;

class WishlistModel {
    private int $userId;
    private int $eventId;
    private DateTime $createdAt;
    private ?DateTime $updatedAt;

}
