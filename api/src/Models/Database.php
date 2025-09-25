<?php
namespace App\Models;

class Database
{
    private static $connection = null;

    public static function getConnection()
    {
        if (self::$connection === null) {
            // Charger la configuration
            $config = require __DIR__ . '/../../config/config.php';

            try {
                self::$connection = new \PDO(
                    "mysql:host={$config['DB_HOST']};dbname={$config['DB_NAME']};charset=utf8mb4",
                    $config['DB_USER'],
                    $config['DB_PASS']
                );
                self::$connection->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
            } catch (\PDOException $e) {
                die("Connection failed: " . $e->getMessage());
            }
        }

        return self::$connection;
    }
}
