<?php

namespace App\Models\Entity;
use DateTime;

class EventModel {
    private int $id;
    private string $name;
    private string $description;
    private DateTime $startDate;
    private DateTime $endDate;
    private string $location;
    private int $userId;

}
