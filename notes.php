<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'notes';
require_once 'includes/header.php';

$pageTitle = 'Love Notes';
$pdo = getDB();

$notes = $pdo->query('SELECT * FROM love_notes ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
$showForm = isset($_GET['add']) || isset($_GET['edit']);
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editNote = null;
if ($editId) {
    $st = $pdo->prepare('SELECT * FROM love_notes WHERE id = ?');
    $st->execute([$editId]);
    $editNote = $st->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="page-header flex-between">
    <h1><i class="fas fa-sticky-note"></i> Love Notes</h1>
    <a href="notes.php?add=1" class="btn btn-primary"><i class="fas fa-plus"></i> Add Note</a>
</div>

<?php if ($showForm): ?>
<div class="card form-card">
    <h2><?php echo $editNote ? 'Edit Note' : 'New Love Note'; ?></h2>
    <form method="post" action="note_save.php">
        <?php if ($editNote): ?><input type="hidden" name="id" value="<?php echo $editNote['id']; ?>"><?php endif; ?>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editNote['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($editNote['content'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="notes.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="notes-grid">
    <?php foreach ($notes as $n): ?>
        <article class="note-card animate-fade-in">
            <h3><?php echo htmlspecialchars($n['title']); ?></h3>
            <p class="note-content"><?php echo nl2br(htmlspecialchars($n['content'])); ?></p>
            <div class="note-meta">
                <span><i class="fas fa-clock"></i> <?php echo date('M j, Y', strtotime($n['updated_at'])); ?></span>
                <a href="notes.php?edit=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                <a href="note_delete.php?id=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline" onclick="return confirm('Delete this note?');"><i class="fas fa-trash"></i></a>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php if (empty($notes) && !$showForm): ?>
    <div class="empty-state">
        <i class="fas fa-heart"></i>
        <p>No love notes yet. Write your first one!</p>
        <a href="notes.php?add=1" class="btn btn-primary">Add Love Note</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
