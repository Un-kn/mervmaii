<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'albums';
require_once 'includes/header.php';

$pageTitle = 'Album';
$pdo = getDB();
$id = (int)($_GET['id'] ?? 0);
if (!$id) {
    header('Location: albums.php');
    exit;
}

$stmt = $pdo->prepare('SELECT * FROM albums WHERE id = ?');
$stmt->execute([$id]);
$album = $stmt->fetch(PDO::FETCH_ASSOC);
if (!$album) {
    header('Location: albums.php');
    exit;
}

$photos = $pdo->prepare('SELECT * FROM photos WHERE album_id = ? ORDER BY uploaded_at DESC');
$photos->execute([$id]);
$photos = $photos->fetchAll(PDO::FETCH_ASSOC);

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload'])) {
        if (!empty($_FILES['photo']['name']) && $_FILES['photo']['error'] === UPLOAD_ERR_OK) {
            $ext = strtolower(pathinfo($_FILES['photo']['name'], PATHINFO_EXTENSION));
            if (in_array($ext, ['jpg','jpeg','png','gif','webp','jfif'])) {
                $dir = 'uploads/albums/' . $id;
                if (!is_dir($dir)) mkdir($dir, 0755, true);
                $filename = uniqid() . '.' . $ext;
                if (move_uploaded_file($_FILES['photo']['tmp_name'], $dir . '/' . $filename)) {
                    $caption = trim($_POST['caption'] ?? '');
                    $pdo->prepare('INSERT INTO photos (album_id, filename, caption) VALUES (?, ?, ?)')->execute([$id, 'albums/' . $id . '/' . $filename, $caption]);
                    if (!$album['cover_photo']) {
                        $pdo->prepare('UPDATE albums SET cover_photo = ? WHERE id = ?')->execute(['albums/' . $id . '/' . $filename, $id]);
                    }
                    $success = 'Photo uploaded!';
                    header('Location: album_view.php?id=' . $id);
                    exit;
                }
            }
            $error = 'Invalid file or upload failed.';
        } else {
            $error = 'Please select a photo.';
        }
    } elseif (isset($_POST['delete_photo'])) {
        $pid = (int)$_POST['delete_photo'];
        $p = $pdo->prepare('SELECT filename FROM photos WHERE id = ? AND album_id = ?');
        $p->execute([$pid, $id]);
        $row = $p->fetch(PDO::FETCH_ASSOC);
        if ($row) {
            $path = 'uploads/' . $row['filename'];
            if (file_exists($path)) unlink($path);
            $pdo->prepare('DELETE FROM photos WHERE id = ?')->execute([$pid]);
            if ($album['cover_photo'] === $row['filename']) {
                $first = $pdo->prepare('SELECT filename FROM photos WHERE album_id = ? LIMIT 1');
                $first->execute([$id]);
                $f = $first->fetch(PDO::FETCH_ASSOC);
                $pdo->prepare('UPDATE albums SET cover_photo = ? WHERE id = ?')->execute([$f ? $f['filename'] : null, $id]);
            }
            header('Location: album_view.php?id=' . $id);
            exit;
        }
    }
}
?>
<div class="page-header flex-between">
    <div>
        <a href="albums.php" class="back-link"><i class="fas fa-arrow-left"></i> Albums</a>
        <h1><?php echo htmlspecialchars($album['name']); ?></h1>
        <?php if ($album['description']): ?><p class="muted"><?php echo htmlspecialchars($album['description']); ?></p><?php endif; ?>
    </div>
    <div class="page-header-actions">
        <button type="button" class="btn btn-primary" onclick="document.getElementById('uploadForm').classList.toggle('hidden')">
            <i class="fas fa-upload"></i> Upload Photo
        </button>
        <a href="album_delete.php?id=<?php echo $id; ?>" class="btn btn-outline btn-danger" onclick="return confirm('Delete this entire album and all its photos?');">
            <i class="fas fa-trash"></i> Delete Album
        </a>
    </div>
</div>

<div id="uploadForm" class="card form-card hidden">
    <?php if ($error): ?><div class="alert alert-error"><?php echo htmlspecialchars($error); ?></div><?php endif; ?>
    <form method="post" enctype="multipart/form-data">
        <input type="hidden" name="upload" value="1">
        <div class="form-group">
            <label>Choose photo</label>
            <input type="file" name="photo" accept="image/*" required>
        </div>
        <div class="form-group">
            <label>Caption (optional)</label>
            <input type="text" name="caption" placeholder="Caption">
        </div>
        <button type="submit" class="btn btn-primary">Upload</button>
    </form>
</div>

<div class="photo-grid">
    <?php foreach ($photos as $p): ?>
        <div class="photo-card">
            <a href="uploads/<?php echo htmlspecialchars($p['filename']); ?>" target="_blank" class="photo-link">
                <img src="uploads/<?php echo htmlspecialchars($p['filename']); ?>" alt="" loading="lazy">
            </a>
            <?php if ($p['caption']): ?><p class="photo-caption"><?php echo htmlspecialchars($p['caption']); ?></p><?php endif; ?>
            <form method="post" class="photo-delete" onsubmit="return confirm('Delete this photo?');">
                <input type="hidden" name="delete_photo" value="<?php echo $p['id']; ?>">
                <button type="submit" class="btn btn-sm btn-outline"><i class="fas fa-trash"></i></button>
            </form>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($photos)): ?>
    <div class="empty-state">
        <i class="fas fa-camera"></i>
        <p>No photos in this album. Upload one above!</p>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
