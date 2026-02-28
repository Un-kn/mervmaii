<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: announcements.php');
    exit;
}

$pdo = getDB();
$id = isset($_POST['id']) ? (int)$_POST['id'] : 0;

if ($id) {
    $st = $pdo->prepare('SELECT is_read FROM announcements WHERE id = ?');
    $st->execute([$id]);
    $current = $st->fetchColumn();
    if ($current !== false) {
        $new = $current ? 0 : 1;
        $pdo->prepare('UPDATE announcements SET is_read = ? WHERE id = ?')->execute([$new, $id]);
    }
}

header('Location: announcements.php');
exit;

