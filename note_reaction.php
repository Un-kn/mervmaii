<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['ok' => false]);
    exit;
}

$pdo = getDB();
$noteId = isset($_POST['note_id']) ? (int)$_POST['note_id'] : 0;
$reaction = $_POST['reaction_type'] ?? '';

$allowed = ['like','heart','care','wow','sad','angry','hahaha'];
if (!$noteId || !in_array($reaction, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

$reactor = trim($_SESSION['display_name'] ?? '');
if ($reactor === '') {
    $reactor = $_SESSION['username'] ?? 'Unknown';
}

$st = $pdo->prepare('SELECT id, reaction_type FROM love_note_reactions WHERE note_id = ? AND reactor = ?');
$st->execute([$noteId, $reactor]);
$existing = $st->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ($existing['reaction_type'] === $reaction) {
        $pdo->prepare('DELETE FROM love_note_reactions WHERE id = ?')->execute([$existing['id']]);
    } else {
        $pdo->prepare('UPDATE love_note_reactions SET reaction_type = ?, created_at = NOW() WHERE id = ?')
            ->execute([$reaction, $existing['id']]);
    }
} else {
    $pdo->prepare('INSERT INTO love_note_reactions (note_id, reactor, reaction_type) VALUES (?, ?, ?)')
        ->execute([$noteId, $reactor, $reaction]);
}

echo json_encode(['ok' => true]);
