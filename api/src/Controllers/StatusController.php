<?php

class StatusController {
    public function getStatus() {
        return [
            'status' => 'ok',
            'timestamp' => date('Y-m-d H:i:s')
        ];
    }
}
