<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? $_POST['id'] ?? 0);
if (!$id) {
    header('Location: albums.php');
    exit;
}

$pdo = getDB();
$stmt = $pdo->prepare('SELECT id FROM albums WHERE id = ?');
$stmt->execute([$id]);
if (!$stmt->fetch()) {
    header('Location: albums.php');
    exit;
}

// Delete photo files
$photos = $pdo->prepare('SELECT filename FROM photos WHERE album_id = ?');
$photos->execute([$id]);
while ($row = $photos->fetch(PDO::FETCH_ASSOC)) {
    $path = 'uploads/' . $row['filename'];
    if (file_exists($path)) unlink($path);
}

$pdo->prepare('DELETE FROM photos WHERE album_id = ?')->execute([$id]);
$pdo->prepare('DELETE FROM albums WHERE id = ?')->execute([$id]);

$dir = 'uploads/albums/' . $id;
if (is_dir($dir)) {
    array_map('unlink', glob($dir . '/*'));
    @rmdir($dir);
}

header('Location: albums.php');
exit;
