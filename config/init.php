<?php
session_start();
require_once __DIR__ . '/database.php';

// One-time: ensure default users exist with correct password
$pdo = getDB();
$defaultPassword = 'mervmaii123';
$hash = password_hash($defaultPassword, PASSWORD_DEFAULT);

// Helper to ensure a user + settings row exist
$ensureUser = function (PDO $pdo, string $username, string $displayName, ?string $email = null) use ($hash) {
    $st = $pdo->prepare("SELECT id FROM users WHERE username = ?");
    $st->execute([$username]);
    $uid = $st->fetchColumn();
    if (!$uid) {
        $pdo->prepare("INSERT INTO users (username, password, email, display_name, anniversary_date) VALUES (?, ?, ?, ?, '2024-01-01')")
            ->execute([$username, $hash, $email, $displayName]);
        $uid = $pdo->lastInsertId();
    }
    $st = $pdo->prepare("SELECT id FROM user_settings WHERE user_id = ?");
    $st->execute([$uid]);
    if (!$st->fetchColumn()) {
        $pdo->prepare("INSERT INTO user_settings (user_id, theme) VALUES (?, 'light')")->execute([$uid]);
    }
};

// Shared couple account
$ensureUser($pdo, 'mervmaii', 'Rhea Mae & Mervin', 'mervmaii@couple.local');
// Individual accounts for reactions / authors
$ensureUser($pdo, 'mervin', 'Mervin Parinas');
$ensureUser($pdo, 'rhea', 'Rhea Mae Magallanes');
