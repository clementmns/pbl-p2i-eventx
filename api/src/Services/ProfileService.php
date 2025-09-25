<?php
namespace App\Services;

use App\Models\Repository\ProfileRepository;

class ProfileService {
    private ProfileRepository $repo;

    public function __construct() {
        $this->repo = new ProfileRepository();
    }

    public function getProfile(int $id) {
        return $this->repo->findById($id)?->toArray();
    }

    public function getProfileByUser(int $userId) {
        return $this->repo->findByUserId($userId)?->toArray();
    }

    public function createProfile(array $data) {
        if (empty($data['userId'])) return ['ok'=>false,'error'=>'missing_userId'];
        $id = $this->repo->create($data);
        return ['ok'=>true, 'id'=>$id];
    }

    public function updateProfile(int $id, array $data) {
        return ['ok'=>$this->repo->update($id, $data)];
    }

    public function deleteProfile(int $id) {
        return ['ok'=>$this->repo->delete($id)];
    }
}
