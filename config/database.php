<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'mervmaii');
define('DB_USER', 'root');
define('DB_PASS', '');

function getDB() {
    static $pdo = null;
    if ($pdo === null) {
        try {
            $pdo = new PDO(
                'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch (PDOException $e) {
            die('Database connection failed. Please import mervmaii.sql and check config/database.php');
        }
    }
    return $pdo;
}
