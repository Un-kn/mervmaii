<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$current = $_SESSION['theme'] ?? 'light';
$theme = $current === 'dark' ? 'light' : 'dark';
$pdo = getDB();
$userId = (int)$_SESSION['user_id'];
$pdo->prepare('UPDATE user_settings SET theme = ? WHERE user_id = ?')->execute([$theme, $userId]);
$_SESSION['theme'] = $theme;
$ref = $_SERVER['HTTP_REFERER'] ?? 'dashboard.php';
header('Location: ' . $ref);
exit;
