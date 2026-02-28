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

$photosStmt = $pdo->prepare('SELECT * FROM photos WHERE album_id = ? ORDER BY uploaded_at DESC');
$photosStmt->execute([$id]);
$photos = $photosStmt->fetchAll(PDO::FETCH_ASSOC);

// Preload reactions for all photos in this album
$photoReactions = [];
if (!empty($photos)) {
    $photoIds = array_column($photos, 'id');
    $placeholders = implode(',', array_fill(0, count($photoIds), '?'));
    $st = $pdo->prepare("SELECT photo_id, reactor, reaction_type FROM photo_reactions WHERE photo_id IN ($placeholders)");
    $st->execute($photoIds);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $pid = (int)$r['photo_id'];
        if (!isset($photoReactions[$pid])) {
            $photoReactions[$pid] = [];
        }
        $photoReactions[$pid][] = $r;
    }
}

$error = '';
$success = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['upload'])) {
        if (!empty($_FILES['photo']['name'])) {
            $files = $_FILES['photo'];
            $isMulti = is_array($files['name']);
            $total = $isMulti ? count($files['name']) : 1;
            $dir = 'uploads/albums/' . $id;
            if (!is_dir($dir)) mkdir($dir, 0755, true);
            $allowedExt = ['jpg','jpeg','png','gif','webp','jfif'];
            $caption = trim($_POST['caption'] ?? '');
            $uploadedCount = 0;

            for ($i = 0; $i < $total; $i++) {
                $name = $isMulti ? ($files['name'][$i] ?? '') : $files['name'];
                $tmpName = $isMulti ? ($files['tmp_name'][$i] ?? '') : $files['tmp_name'];
                $errorCode = $isMulti ? ($files['error'][$i] ?? UPLOAD_ERR_NO_FILE) : $files['error'];

                if ($name === '' || $errorCode !== UPLOAD_ERR_OK) {
                    continue;
                }

                $ext = strtolower(pathinfo($name, PATHINFO_EXTENSION));
                if (!in_array($ext, $allowedExt, true)) {
                    continue;
                }

                $filename = uniqid('', true) . '.' . $ext;
                if (move_uploaded_file($tmpName, $dir . '/' . $filename)) {
                    $pdo->prepare('INSERT INTO photos (album_id, filename, caption) VALUES (?, ?, ?)')
                        ->execute([$id, 'albums/' . $id . '/' . $filename, $caption]);

                    if (!$album['cover_photo']) {
                        $pdo->prepare('UPDATE albums SET cover_photo = ? WHERE id = ?')
                            ->execute(['albums/' . $id . '/' . $filename, $id]);
                        // refresh album cover in memory for subsequent iterations
                        $album['cover_photo'] = 'albums/' . $id . '/' . $filename;
                    }
                    $uploadedCount++;
                }
            }

            if ($uploadedCount > 0) {
                $success = $uploadedCount === 1 ? 'Photo uploaded!' : $uploadedCount . ' photos uploaded!';
                header('Location: album_view.php?id=' . $id);
                exit;
            } else {
                $error = 'Invalid file or upload failed.';
            }
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
            <label>Choose photos</label>
            <input type="file" name="photo[]" accept="image/*" multiple required>
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
            <?php
                $pid = (int)$p['id'];
                $reactions = $photoReactions[$pid] ?? [];
                $currentUser = trim($_SESSION['display_name'] ?? ($_SESSION['username'] ?? ''));
                $userReaction = null;
                $counts = ['like' => 0, 'heart' => 0, 'care' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0];
                $namesByType = ['like' => [], 'heart' => [], 'care' => [], 'wow' => [], 'sad' => [], 'angry' => []];
                foreach ($reactions as $r) {
                    $type = $r['reaction_type'];
                    if (!isset($counts[$type])) continue;
                    $counts[$type]++;
                    $namesByType[$type][] = $r['reactor'];
                    if ($currentUser !== '' && $r['reactor'] === $currentUser) {
                        $userReaction = $type;
                    }
                }
                $reactionEmoji = [
                    'like' => '👍',
                    'heart' => '❤️',
                    'care' => '🤗',
                    'wow' => '😮',
                    'sad' => '😢',
                    'angry' => '😡',
                ];
                $mainLabel = $userReaction ? ucfirst($userReaction) : 'Like';
                $mainEmoji = $userReaction ? $reactionEmoji[$userReaction] : '👍';
            ?>
            <div class="reaction-container" data-reaction-context="photo" data-id="<?php echo $pid; ?>">
                <button type="button" class="reaction-main-btn" data-current="<?php echo htmlspecialchars($userReaction ?? ''); ?>">
                    <span class="reaction-main-emoji"><?php echo $mainEmoji; ?></span>
                    <span class="reaction-main-label"><?php echo htmlspecialchars($mainLabel); ?></span>
                </button>
                <div class="reaction-picker">
                    <?php foreach ($reactionEmoji as $type => $emoji): ?>
                        <button type="button" class="reaction-option" data-type="<?php echo $type; ?>">
                            <span class="emoji"><?php echo $emoji; ?></span>
                            <span class="label"><?php echo ucfirst($type); ?></span>
                        </button>
                    <?php endforeach; ?>
                </div>
                <?php
                    $totalReactions = array_sum($counts);
                    if ($totalReactions > 0):
                        $summaryParts = [];
                        foreach ($namesByType as $type => $names) {
                            if (empty($names)) continue;
                            $summaryParts[] = $reactionEmoji[$type] . ' ' . implode(', ', array_unique($names));
                        }
                ?>
                    <div class="reaction-summary">
                        <span class="reaction-count"><?php echo $totalReactions; ?> reaction<?php echo $totalReactions > 1 ? 's' : ''; ?></span>
                        <span class="reaction-names"><?php echo htmlspecialchars(implode(' • ', $summaryParts)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
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
