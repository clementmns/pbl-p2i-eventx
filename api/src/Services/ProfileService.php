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

    public function upsertProfile(int $userId, array $data) {
        $profile = $this->repo->findByUserId($userId);
        if ($profile) {
            $ok = $this->repo->update($profile->id, $data);
            return ['ok'=>$ok, 'action'=>'updated', 'id'=>$profile->id];
        } else {
            $data['userId'] = $userId;
            $id = $this->repo->create($data);
            return ['ok'=>true, 'action'=>'created', 'id'=>$id];
        }
    }

    public function deleteProfile(int $id) {
        return ['ok'=>$this->repo->delete($id)];
    }
}
