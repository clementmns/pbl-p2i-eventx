<?php
$db = new mysqli($_ENV["DB_HOST"], $_ENV["DB_USER"], $_ENV["DB_PASS"], $_ENV["DB_NAME"]);

if ($db->connect_error) {
  die("Connection failed: " . $db->connect_error);
}
