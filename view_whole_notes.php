<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'notes';
require_once 'includes/header.php';

$pageTitle = 'View Love Note';
$pdo = getDB();

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if (!$id) {
    header('Location: notes.php');
    exit;
}

$st = $pdo->prepare('SELECT * FROM love_notes WHERE id = ?');
$st->execute([$id]);
$note = $st->fetch(PDO::FETCH_ASSOC);
if (!$note) {
    header('Location: notes.php');
    exit;
}
?>
<div class="page-header">
    <a href="notes.php" class="back-link"><i class="fas fa-arrow-left"></i> Back to Love Notes</a>
    <h1><i class="fas fa-sticky-note"></i> Full Love Note</h1>
</div>

<div class="card">
    <h2><?php echo htmlspecialchars($note['title']); ?></h2>
    <p class="muted">
        <i class="fas fa-user"></i>
        <?php echo htmlspecialchars($note['author_name'] ?: 'Unknown'); ?>
        &nbsp;·&nbsp;
        <i class="fas fa-clock"></i>
        <?php echo date('M j, Y g:i A', strtotime($note['created_at'])); ?>
    </p>
    <div class="note-content-full">
        <?php echo nl2br(htmlspecialchars($note['content'])); ?>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

