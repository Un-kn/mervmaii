<?php

//for localhost
define('DB_HOST', 'localhost');
define('DB_NAME', 'mervmaii');
define('DB_USER', 'root');
define('DB_PASS', '');

//for live server
// define('DB_HOST', 'sql209.infinityfree.com');
// define('DB_NAME', 'if0_41218263_mervmaii');
// define('DB_USER', 'if0_41218263');
// define('DB_PASS', 'zsS0ddFcTr');

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
// Track user activity (update last_activity timestamp)
function updateUserActivity($userId) {
    try {
        $pdo = getDB();
        $st = $pdo->prepare("UPDATE users SET last_activity = CURRENT_TIMESTAMP WHERE id = ?");
        $st->execute([$userId]);
    } catch (Exception $e) {
        // Silently fail - not critical
    }
}

// Check if user is currently online (active in last 5 minutes)
function isUserOnline($userId) {
    try {
        $pdo = getDB();
        $st = $pdo->prepare("SELECT last_activity FROM users WHERE id = ?");
        $st->execute([$userId]);
        $result = $st->fetch(PDO::FETCH_ASSOC);
        if (!$result) return false;
        
        $lastActivity = strtotime($result['last_activity']);
        $now = time();
        $fiveMinutesAgo = $now - (5 * 60);
        
        return $lastActivity >= $fiveMinutesAgo;
    } catch (Exception $e) {
        return false;
    }
}


