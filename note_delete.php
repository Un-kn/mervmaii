<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
$authorName = $_SESSION['display_name'] ?? null;

if ($id && $authorName) {
    $pdo = getDB();
    // Verify that current user is the original author before allowing delete
    $st = $pdo->prepare('SELECT author_name FROM love_notes WHERE id = ?');
    $st->execute([$id]);
    $note = $st->fetch(PDO::FETCH_ASSOC);
    
    if ($note && $note['author_name'] === $authorName) {
        // Authorized - delete the note
        $pdo->prepare('DELETE FROM love_notes WHERE id = ?')->execute([$id]);

        header('Location: notes.php');
    }
}