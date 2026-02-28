<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$pdo = getDB();
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: notes.php');
    exit;
}

$id = (int)($_POST['id'] ?? 0);
$title = trim($_POST['title'] ?? '');
$content = trim($_POST['content'] ?? '');
if ($title === '' || $content === '') {
    header('Location: notes.php?add=1');
    exit;
}

$authorName = $_SESSION['display_name'] ?? null;

if ($id) {
    $pdo->prepare('UPDATE love_notes SET title = ?, content = ?, author_name = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$title, $content, $authorName, $id]);
} else {
    $pdo->prepare('INSERT INTO love_notes (title, content, author_name) VALUES (?, ?, ?)')
        ->execute([$title, $content, $authorName]);
}
header('Location: notes.php');
exit;
