<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'albums';
require_once 'includes/header.php';

$pageTitle = 'Albums';
$pdo = getDB();

$albums = $pdo->query("
    SELECT a.*, COUNT(p.id) AS photo_count
    FROM albums a
    LEFT JOIN photos p ON p.album_id = a.id
    GROUP BY a.id
    ORDER BY a.created_at DESC
")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header flex-between">
    <h1><i class="fas fa-images"></i> Albums</h1>
    <a href="album_add.php" class="btn btn-primary"><i class="fas fa-plus"></i> Add Album</a>
</div>

<div class="album-grid">
    <?php foreach ($albums as $a): ?>
        <a href="album_view.php?id=<?php echo (int)$a['id']; ?>" class="album-card animate-slide-up">
            <div class="album-cover">
                <?php if ($a['cover_photo']): ?>
                    <img src="uploads/<?php echo htmlspecialchars($a['cover_photo']); ?>" alt="">
                <?php else: ?>
                    <div class="album-cover-placeholder"><i class="fas fa-images"></i></div>
                <?php endif; ?>
                <span class="album-count"><?php echo (int)$a['photo_count']; ?> photos</span>
            </div>
            <div class="album-info">
                <h3><?php echo htmlspecialchars($a['name']); ?></h3>
                <?php if (!empty($a['description'])): ?>
                    <p class="muted"><?php echo htmlspecialchars(substr($a['description'], 0, 60)); ?><?php echo strlen($a['description']) > 60 ? '…' : ''; ?></p>
                <?php endif; ?>
            </div>
        </a>
    <?php endforeach; ?>
</div>

<?php if (empty($albums)): ?>
    <div class="empty-state">
        <i class="fas fa-folder-open"></i>
        <p>No albums yet. Create your first album!</p>
        <a href="album_add.php" class="btn btn-primary">Add Album</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
