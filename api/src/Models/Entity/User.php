<?php
namespace App\Models\Entity;

class User {
    public ?int $id;
    public string $mail;
    public string $passwordHash;
    public bool $isActive;
    public ?int $roleId;
    public $profile = null;

    public function __construct(?int $id, string $mail, string $passwordHash, bool $isActive = true, ?int $roleId = null) {
        $this->id = $id;
        $this->mail = $mail;
        $this->passwordHash = $passwordHash;
        $this->isActive = $isActive;
        $this->roleId = $roleId;
    }

    public function toArray(): array {
        $arr = [
            'id' => $this->id,
            'mail' => $this->mail,
            'isActive' => $this->isActive,
            'roleId' => $this->roleId
        ];
        if ($this->profile) {
            $arr['profile'] = is_object($this->profile) && method_exists($this->profile, 'toArray') ? $this->profile->toArray() : $this->profile;
        }
        return $arr;
    }
}
