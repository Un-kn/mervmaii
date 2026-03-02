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
    // Verify that current user is the original author before allowing edit
    $st = $pdo->prepare('SELECT author_name FROM love_notes WHERE id = ?');
    $st->execute([$id]);
    $existingNote = $st->fetch(PDO::FETCH_ASSOC);
    
    if (!$existingNote || $existingNote['author_name'] !== $authorName) {
        // Unauthorized - not the original author
        header('Location: notes.php');
        exit;
    }
    
    $pdo->prepare('UPDATE love_notes SET title = ?, content = ?, author_name = ?, updated_at = NOW() WHERE id = ?')
        ->execute([$title, $content, $authorName, $id]);
} else {
    $pdo->prepare('INSERT INTO love_notes (title, content, author_name) VALUES (?, ?, ?)')
        ->execute([$title, $content, $authorName]);
}
header('Location: notes.php');
exit;
