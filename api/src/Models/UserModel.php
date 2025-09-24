<?php
class UserModel {
    private int $id;
    private string $mail;
    private string $password;
    private bool $isAdmin;
    private DateTime $createdAt;
    private ?DateTime $updatedAt;
    private int $roleId;

}
