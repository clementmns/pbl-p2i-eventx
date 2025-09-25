<?php

namespace App\Models;

use PDO;

class BaseDataAccess
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}


