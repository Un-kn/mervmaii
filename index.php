<?php
require_once 'config/init.php';

if (isset($_SESSION['user_id'])) {
    header('Location: dashboard.php');
    exit;
}

$error = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = trim($_POST['username'] ?? '');
    $password = $_POST['password'] ?? '';
    if ($username === '' || $password === '') {
        $error = 'Please enter username and password.';
    } else {
        $pdo = getDB();
        $stmt = $pdo->prepare('SELECT id, username, password, display_name FROM users WHERE username = ?');
        $stmt->execute([$username]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if ($user && password_verify($password, $user['password'])) {
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['username'] = $user['username'];
            $_SESSION['display_name'] = $user['display_name'];
            $st = $pdo->prepare('SELECT theme FROM user_settings WHERE user_id = ?');
            $st->execute([$user['id']]);
            $row = $st->fetch(PDO::FETCH_ASSOC);
            $_SESSION['theme'] = $row ? $row['theme'] : 'light';
            header('Location: dashboard.php');
            exit;
        }
        $error = 'Invalid username or password.';
    }
}

$loginImage = 'assets/images/login-couple.png';
if (!file_exists($loginImage)) {
    $alt = 'assets/c__Users_DELL_AppData_Roaming_Cursor_User_workspaceStorage_874e4f62f984536318012a72d25ca2a4_images_rm18-a5172672-44a3-492d-b138-070793c2cf9a.png';
    $loginImage = file_exists($alt) ? $alt : 'assets/images/login-couple.jfif';
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<meta name="mobile-web-app-capable" content="yes">
    <title>Login - Mervmaii</title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="login-page">
    <div class="login-container animate-fade-in">
        <div class="login-left">
            <div class="login-image-wrap">
                <img src="<?php echo htmlspecialchars($loginImage); ?>" alt="Rhea Mae & Mervin" class="login-image">
                <div class="login-image-overlay"></div>
                <div class="login-badge">
                    <i class="fas fa-heart"></i> Mervmaii
                </div>
            </div>
        </div>
        <div class="login-right">
            <div class="login-form-wrap">
                <h1 class="login-title">
                    <i class="fas fa-heart"></i> Mervmaii
                </h1>
                <p class="login-subtitle">Rhea Mae & Mervin — Our Couple Space</p>
                <?php if ($error): ?>
                    <div class="alert alert-error animate-shake"><?php echo htmlspecialchars($error); ?></div>
                <?php endif; ?>
                <form method="post" action="index.php" class="login-form">
                    <div class="form-group">
                        <label for="username"><i class="fas fa-user"></i> Username</label>
                        <input type="text" id="username" name="username" required autofocus
                               placeholder="mervmaii">
                    </div>
                    <div class="form-group">
                        <label for="password"><i class="fas fa-lock"></i> Password</label>
                        <input type="password" id="password" name="password" required placeholder="••••••••">
                    </div>
                    <button type="submit" class="btn btn-primary btn-block btn-login">
                        <i class="fas fa-sign-in-alt"></i> Login
                    </button>
                </form>
                <p class="login-hint">Default: mervmaii / mervmaii123 — change in Settings after first login.</p>
            </div>
        </div>
    </div>
    <div class="login-bg-hearts"></div>
    <script src="assets/js/main.js"></script>
    <script>
    (function() {
        var form = document.querySelector('.login-form');
        if (form) {
            form.querySelectorAll('input').forEach(function(input) {
                input.addEventListener('focus', function() {
                    setTimeout(function() {
                        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
                    }, 300);
                });
            });
        }
    })();
    </script>
</body>
</html>
