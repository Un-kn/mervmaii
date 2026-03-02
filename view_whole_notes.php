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

$st = $pdo->prepare(
    'SELECT ln.*, u.profile_picture, u.id as user_id
     FROM love_notes ln
     LEFT JOIN users u ON u.id = (
         SELECT id FROM users WHERE display_name = ln.author_name LIMIT 1
     )
     WHERE ln.id = ?'
);
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
    <div class="note-meta-full" style="display: flex; gap: 15px; align-items: center; margin-bottom: 20px;">
        <!-- Profile Picture -->
        <div style="position: relative;">
            <?php if ($note['profile_picture']): ?>
                <img src="uploads/profiles/<?php echo htmlspecialchars($note['profile_picture']); ?>" alt="<?php echo htmlspecialchars($note['author_name']); ?>" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; border: 2px solid #ddd;">
            <?php else: ?>
                <div style="width: 80px; height: 80px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; border: 2px solid #ddd;">
                    <i class="fas fa-user" style="font-size: 40px; color: #999;"></i>
                </div>
            <?php endif; ?>
            <!-- Online indicator -->
            <?php if ($note['user_id'] && isUserOnline($note['user_id'])): ?>
                <span style="position: absolute; bottom: 0; right: 0; width: 20px; height: 20px; background-color: #4caf50; border: 3px solid white; border-radius: 50%; display: block;"></span>
            <?php endif; ?>
        </div>
        
        <!-- Author info -->
        <div>
            <div style="font-weight: 600; font-size: 1.1em;">
                <?php echo htmlspecialchars($note['author_name'] ?: 'Unknown'); ?>
            </div>
            <div class="muted" style="font-size: 0.9em;">
                <i class="fas fa-clock"></i>
                <?php echo htmlspecialchars(date('M j, Y g:i A', strtotime($note['created_at']))); ?>
            </div>
        </div>
    </div>
     <h2><?php echo htmlspecialchars($note['title']); ?></h2>
    <div class="note-content-full">
        <p class="p-content"><?php echo nl2br(htmlspecialchars($note['content'])); ?></p>
    </div>
</div>

<?php require_once 'includes/footer.php'; ?>

