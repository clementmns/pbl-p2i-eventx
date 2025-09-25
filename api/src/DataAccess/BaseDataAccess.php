<?php

namespace App\DataAccess;

use PDO;

class BaseDataAccess
{
    protected $db;

    public function __construct(PDO $db)
    {
        $this->db = $db;
    }
}


