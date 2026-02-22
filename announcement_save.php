<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: announcements.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
$schedule_date = $_POST['schedule_date'] ?? '';
$schedule_time = !empty($_POST['schedule_time']) ? $_POST['schedule_time'] : null;
$location = trim($_POST['location'] ?? '') ?: null;
if ($title === '' || $schedule_date === '') {
    header('Location: announcements.php?add=1');
    exit;
}

if ($id) {
    $pdo->prepare('UPDATE announcements SET title = ?, content = ?, schedule_date = ?, schedule_time = ?, location = ? WHERE id = ?')
        ->execute([$title, $content, $schedule_date, $schedule_time, $location, $id]);
} else {
    $pdo->prepare('INSERT INTO announcements (title, content, schedule_date, schedule_time, location) VALUES (?, ?, ?, ?, ?)')
        ->execute([$title, $content, $schedule_date, $schedule_time, $location]);
}
header('Location: announcements.php');
exit;
