<?php
session_start();
require_once __DIR__ . '/database.php';

// One-time: ensure default user exists with correct password
$pdo = getDB();
$stmt = $pdo->query("SELECT COUNT(*) FROM users");
if ($stmt && (int)$stmt->fetchColumn() === 0) {
    $hash = password_hash('mervmaii123', PASSWORD_DEFAULT);
    $pdo->prepare("INSERT INTO users (username, password, email, display_name, anniversary_date) VALUES ('mervmaii', ?, 'mervmaii@couple.local', 'Rhea Mae & Mervin', '2024-01-01')")->execute([$hash]);
    $uid = $pdo->lastInsertId();
    $pdo->prepare("INSERT INTO user_settings (user_id, theme) VALUES (?, 'light')")->execute([$uid]);
}
