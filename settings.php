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

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
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
        }
    } elseif (isset($_POST['update_profile'])) {
        $display_name = trim($_POST['display_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $anniversary_date = $_POST['anniversary_date'] ?? '';
        if ($display_name !== '') {
            $pdo->prepare('UPDATE users SET display_name = ?, email = ?, anniversary_date = ? WHERE id = ?')
                ->execute([$display_name, $email, $anniversary_date ?: null, $userId]);
            $_SESSION['display_name'] = $display_name;
            $msg = 'Profile updated.';
        }
    } elseif (isset($_POST['theme'])) {
        $theme = $_POST['theme'] === 'dark' ? 'dark' : 'light';
        $pdo->prepare('UPDATE user_settings SET theme = ? WHERE user_id = ?')->execute([$theme, $userId]);
        $_SESSION['theme'] = $theme;
        $msg = 'Theme saved.';
        header('Location: settings.php');
        exit;
    }
}

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
    <div class="card">
        <h2><i class="fas fa-user"></i> Profile</h2>
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
            <button type="submit" class="btn btn-primary">Save Profile</button>
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
            <button type="submit" class="btn btn-primary">Update Password</button>
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
<?php require_once 'includes/footer.php'; ?>
