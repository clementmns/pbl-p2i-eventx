<?php
namespace App\Controllers;

use App\Services\ProfileService;
use JsonException;

class ProfileController {
    private ProfileService $service;

    public function __construct() {
        $this->service = new ProfileService();
    }

    /**
     * @throws JsonException
     */
    public function getProfile(int $id) {
        echo json_encode($this->service->getProfile($id), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function getProfileByUser(int $userId) {
        echo json_encode($this->service->getProfileByUser($userId), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    /**
     * @throws JsonException
     */
    public function upsertProfile(int $userId, array $data) {
        echo json_encode($this->service->upsertProfile($userId, $data), JSON_THROW_ON_ERROR);
    }

    /**
     * @throws JsonException
     */
    public function deleteProfile(int $id) {
        echo json_encode($this->service->deleteProfile($id), JSON_THROW_ON_ERROR);
    }
}
