<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'settings';
require_once 'includes/header.php';

$pageTitle = 'Settings';
$pdo = getDB();
$userId = (int)$_SESSION['user_id'];

$st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
$st->execute([$userId]);
$user = $st->fetch(PDO::FETCH_ASSOC);

$msg = '';
$err = '';

// flash messages from redirect
if (isset($_SESSION['flash_msg'])) {
    $msg = $_SESSION['flash_msg'];
    unset($_SESSION['flash_msg']);
}
if (isset($_SESSION['flash_err'])) {
    $err = $_SESSION['flash_err'];
    unset($_SESSION['flash_err']);
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $redirect = false;

    // CHANGE PASSWORD
    if (isset($_POST['change_password'])) {
        $current = $_POST['current_password'] ?? '';
        $new = $_POST['new_password'] ?? '';
        $confirm = $_POST['confirm_password'] ?? '';

        if (!password_verify($current, $user['password'])) {
            $err = 'Current password is incorrect.';
        } elseif (strlen($new) < 6) {
            $err = 'New password must be at least 6 characters.';
        } elseif ($new !== $confirm) {
            $err = 'New passwords do not match.';
        } else {
            $hash = password_hash($new, PASSWORD_DEFAULT);
            $pdo->prepare('UPDATE users SET password = ? WHERE id = ?')->execute([$hash, $userId]);
            $msg = 'Password updated successfully.';
            $redirect = true;
        }
    }

    // UPDATE PROFILE TEXT INFO
    elseif (isset($_POST['update_profile'])) {
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $anniversary_date = $_POST['anniversary_date'] ?? '';

        if ($display_name !== '') {
            $pdo->prepare('UPDATE users SET display_name = ?, email = ?, anniversary_date = ? WHERE id = ?')
                ->execute([$display_name, $email, $anniversary_date ?: null, $userId]);

            $_SESSION['display_name'] = $display_name;
            $msg = 'Profile updated.';
            $redirect = true;
        }
    }

    // HANDLE PROFILE PICTURE UPLOAD (separate form trigger)
    elseif (isset($_POST['upload_picture']) && isset($_FILES['profile_picture'])) {
        if ($_FILES['profile_picture']['error'] !== UPLOAD_ERR_OK) {
            $err = 'Upload error: ' . $_FILES['profile_picture']['error'];
        } else {
            $file = $_FILES['profile_picture'];
            $allowed = ['image/jpeg', 'image/png', 'image/gif', 'image/webp'];
            $maxSize = 5 * 1024 * 1024;
            if (!in_array($file['type'], $allowed)) {
                $err = 'Only JPEG, PNG, GIF, and WebP images are allowed.';
            } elseif ($file['size'] > $maxSize) {
                $err = 'Image must be less than 5MB.';
            } else {
                if (!empty($user['profile_picture'])) {
                    $oldPath = __DIR__ . '/uploads/profiles/' . $user['profile_picture'];
                    if (file_exists($oldPath)) unlink($oldPath);
                }
                $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
                $filename = uniqid('profile_') . '.' . $ext;
                $uploadDir = __DIR__ . '/uploads/profiles/';
                if (!is_dir($uploadDir)) mkdir($uploadDir,0755,true);
                $uploadPath = $uploadDir . $filename;
                if (move_uploaded_file($file['tmp_name'],$uploadPath)) {
                    $pdo->prepare('UPDATE users SET profile_picture = ? WHERE id = ?')->execute([$filename,$userId]);
                    $msg = 'Profile picture updated.';
                    $redirect = true;
                    $st = $pdo->prepare('SELECT * FROM users WHERE id = ?');
                    $st->execute([$userId]);
                    $user = $st->fetch(PDO::FETCH_ASSOC);
                } else {
                    $err = 'Failed to move uploaded file.';
                }
            }
        }
    }

    // THEME UPDATE
    elseif (isset($_POST['theme'])) {
        $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
        $pdo->prepare('UPDATE user_settings SET theme = ? WHERE user_id = ?')->execute([$theme, $userId]);
        $_SESSION['theme'] = $theme;
        $msg = 'Theme saved.';
        $redirect = true;
    }
}

// POST redirect handling
if (!empty($redirect)) {
    if ($msg) {
        $_SESSION['flash_msg'] = $msg;
    }
    if ($err) {
        $_SESSION['flash_err'] = $err;
    }
    header('Location: settings.php');
    exit;
}

// Get theme again
$st = $pdo->prepare('SELECT theme FROM user_settings WHERE user_id = ?');
$st->execute([$userId]);
$theme = $st->fetchColumn() ?: 'light';
?>
<div class="page-header">
    <h1><i class="fas fa-cog"></i> Settings</h1>
</div>

<?php if ($msg): ?><div class="alert alert-success"><?php echo htmlspecialchars($msg); ?></div><?php endif; ?>
<?php if ($err): ?><div class="alert alert-error"><?php echo htmlspecialchars($err); ?></div><?php endif; ?>

<div class="settings-grid">
    <!-- picture card -->
    <div class="card">
        <h2><i class="fas fa-user"></i> Profile Picture</h2>
        <form method="post" enctype="multipart/form-data" id="pictureForm">
            <input type="hidden" name="upload_picture" value="1">
            <div class="form-group">
                <div style="margin-bottom: 15px;">
                    <?php if ($user['profile_picture']): ?>
                        <img src="uploads/profiles/<?php echo htmlspecialchars($user['profile_picture']); ?>" alt="Profile" id="picturePreview" style="width: 80px; height: 80px; border-radius: 50%; object-fit: cover; margin-bottom: 10px;">
                    <?php else: ?>
                        <div id="picturePreview" style="width: 80px; height: 80px; border-radius: 50%; background-color: #ddd; display: flex; align-items: center; justify-content: center; margin-bottom: 10px;">
                            <i class="fas fa-user" style="font-size: 40px; color: #999;"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="form-group">
                    <label>Choose New Picture</label>
                    <input type="file" name="profile_picture" id="profilePicInput" accept="image/*">
                    <p class="muted" style="font-size:0.9em;">Max 5MB, JPG/PNG/GIF/WebP</p>
                </div>
                <button type="submit" class="btn btn-primary"><i class="fas fa-cloud-upload"></i> Upload Picture</button>
            </div>
        </form>
    </div>

    <!-- text profile card -->
    <div class="card">
        <h2><i class="fas fa-user"></i> Profile Info</h2>
        <form method="post">
            <input type="hidden" name="update_profile" value="1">
            <div class="form-group">
                <label>Display Name</label>
                <input type="text" name="display_name" value="<?php echo htmlspecialchars($user['display_name'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Email (optional)</label>
                <input type="email" name="email" value="<?php echo htmlspecialchars($user['email'] ?? ''); ?>">
            </div>
            <div class="form-group">
                <label>Anniversary Date (start of relationship)</label>
                <input type="date" name="anniversary_date" value="<?php echo htmlspecialchars($user['anniversary_date'] ?? ''); ?>">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Profile</button>
        </form>
    </div>

    <div class="card">
        <h2><i class="fas fa-lock"></i> Change Password</h2>
        <form method="post">
            <input type="hidden" name="change_password" value="1">
            <div class="form-group">
                <label>Current Password</label>
                <input type="password" name="current_password" required>
            </div>
            <div class="form-group">
                <label>New Password</label>
                <input type="password" name="new_password" required minlength="6">
            </div>
            <div class="form-group">
                <label>Confirm New Password</label>
                <input type="password" name="confirm_password" required minlength="6">
            </div>
            <button type="submit" class="btn btn-primary"><i class="fas fa-key"></i> Update Password</button>
        </form>
    </div>

    <div class="card">
        <h2><i class="fas fa-moon"></i> Appearance</h2>
        <form method="post" id="themeForm">
            <input type="hidden" name="theme" id="themeInput" value="<?php echo htmlspecialchars($theme); ?>">
            <div class="theme-options">
                <label class="theme-option <?php echo $theme === 'light' ? 'active' : ''; ?>" data-theme="light">
                    <i class="fas fa-sun"></i> Light
                </label>
                <label class="theme-option <?php echo $theme === 'dark' ? 'active' : ''; ?>" data-theme="dark">
                    <i class="fas fa-moon"></i> Dark
                </label>
            </div>
            <p class="muted">Theme is applied immediately and saved.</p>
        </form>
    </div>
</div>
<script>
document.querySelectorAll('.theme-option').forEach(function(el) {
    el.addEventListener('click', function() {
        var theme = this.getAttribute('data-theme');
        document.getElementById('themeInput').value = theme;
        document.documentElement.setAttribute('data-theme', theme);
        document.querySelectorAll('.theme-option').forEach(function(o) { o.classList.remove('active'); });
        this.classList.add('active');
        document.getElementById('themeForm').submit();
    });
});
</script>
<script>
// preview selected profile picture
var picInput = document.getElementById('profilePicInput');
if (picInput) {
    picInput.addEventListener('change', function(e) {
        var file = this.files[0];
        if (!file) return;
        var reader = new FileReader();
        reader.onload = function(evt) {
            var preview = document.getElementById('picturePreview');
            if (preview.tagName.toLowerCase() === 'img') {
                preview.src = evt.target.result;
            } else {
                // replace placeholder div with img
                var img = document.createElement('img');
                img.id = 'picturePreview';
                img.style.width = '80px';
                img.style.height = '80px';
                img.style.borderRadius = '50%';
                img.style.objectFit = 'cover';
                img.style.marginBottom = '10px';
                img.src = evt.target.result;
                preview.parentNode.replaceChild(img, preview);
            }
        };
        reader.readAsDataURL(file);
    });
}
</script>
<?php require_once 'includes/footer.php'; ?>
