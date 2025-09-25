<?php

namespace App\Controllers;

class StatusController
{
    /**
     * Get the current status of the application.
     */
    public function getStatus()
    {
        return [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
