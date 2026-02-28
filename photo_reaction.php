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
$photoId = isset($_POST['photo_id']) ? (int)$_POST['photo_id'] : 0;
$reaction = $_POST['reaction_type'] ?? '';

$allowed = ['like','heart','care','wow','sad','angry','hahaha'];
if (!$photoId || !in_array($reaction, $allowed, true)) {
    http_response_code(400);
    echo json_encode(['ok' => false]);
    exit;
}

// Use display name of logged-in account for reactor label
$reactor = trim($_SESSION['display_name'] ?? '');
if ($reactor === '') {
    $reactor = $_SESSION['username'] ?? 'Unknown';
}

// Upsert reaction: if same type again, remove (toggle off), else update to new type
$st = $pdo->prepare('SELECT id, reaction_type FROM photo_reactions WHERE photo_id = ? AND reactor = ?');
$st->execute([$photoId, $reactor]);
$existing = $st->fetch(PDO::FETCH_ASSOC);

if ($existing) {
    if ($existing['reaction_type'] === $reaction) {
        $pdo->prepare('DELETE FROM photo_reactions WHERE id = ?')->execute([$existing['id']]);
    } else {
        $pdo->prepare('UPDATE photo_reactions SET reaction_type = ?, created_at = NOW() WHERE id = ?')
            ->execute([$reaction, $existing['id']]);
    }
} else {
    $pdo->prepare('INSERT INTO photo_reactions (photo_id, reactor, reaction_type) VALUES (?, ?, ?)')
        ->execute([$photoId, $reactor, $reaction]);
}

echo json_encode(['ok' => true]);
