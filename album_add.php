<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'albums';
require_once 'includes/header.php';

$pageTitle = 'Add Album';
$pdo = getDB();
$error = '';
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $description = trim($_POST['description'] ?? '');
    if ($name === '') {
        $error = 'Album name is required.';
    } else {
        $stmt = $pdo->prepare('INSERT INTO albums (name, description) VALUES (?, ?)');
        $stmt->execute([$name, $description]);
        header('Location: album_view.php?id=' . $pdo->lastInsertId());
        exit;
    }
}
?>
<div class="page-header">
    <h1><i class="fas fa-plus-circle"></i> Add Album</h1>
</div>

<div class="card form-card">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" action="album_add.php">
        <div class="form-group">
            <label for="name">Album Name</label>
            <input type="text" id="name" name="name" required value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="description">Description (optional)</label>
            <textarea id="description" name="description" rows="3"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Create Album</button>
        <a href="albums.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php require_once 'includes/footer.php'; ?>
