<?php
require_once __DIR__ . '/../vendor/autoload.php';

$db = null;
try {
  $db = new PDO(
    "mysql:host=" . getenv("DB_HOST") . ";dbname=" . getenv("DB_NAME") . ";charset=utf8mb4",
    getenv("DB_USER"),
    getenv("DB_PASS")
  );
  $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
  die("Connection failed: " . $e->getMessage());
}
return $db;
