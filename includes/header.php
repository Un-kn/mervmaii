<?php
$theme = $_SESSION['theme'] ?? 'light';
$upcomingCount = 0;
if (isset($pdo) && $pdo) {
    $upcomingCount = (int)getDB()->query("SELECT COUNT(*) FROM announcements WHERE schedule_date >= CURDATE()")->fetchColumn();
} elseif (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/../config/database.php';
    $upcomingCount = (int)getDB()->query("SELECT COUNT(*) FROM announcements WHERE schedule_date >= CURDATE()")->fetchColumn();
}
?>
<!DOCTYPE html>
<html lang="en" data-theme="<?php echo htmlspecialchars($theme); ?>">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mervmaii - <?php echo htmlspecialchars($pageTitle ?? 'Couple System'); ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body class="app-body">
    <nav class="navbar">
        <a href="dashboard.php" class="nav-brand">
            <i class="fas fa-heart"></i> Mervmaii
        </a>
        <div class="nav-right">
            <button class="nav-toggle" id="navToggle" aria-label="Toggle navigation">
                <i class="fas fa-bars"></i>
            </button>
            <ul class="nav-menu">
            <li><a href="dashboard.php" class="<?php echo $page === 'dashboard' ? 'active' : ''; ?>"><i class="fas fa-home"></i> Dashboard</a></li>
            <li><a href="albums.php" class="<?php echo $page === 'albums' ? 'active' : ''; ?>"><i class="fas fa-images"></i> Albums</a></li>
            <li><a href="notes.php" class="<?php echo $page === 'notes' ? 'active' : ''; ?>"><i class="fas fa-sticky-note"></i> Love Notes</a></li>
            <li><a href="announcements.php" class="<?php echo $page === 'announcements' ? 'active' : ''; ?>"><i class="fas fa-bell"></i> Announcements<?php if ($upcomingCount > 0): ?><span class="notification-dot nav-dot" title="<?php echo $upcomingCount; ?> upcoming"></span><?php endif; ?></a></li>
            <li><a href="settings.php" class="<?php echo $page === 'settings' ? 'active' : ''; ?>"><i class="fas fa-cog"></i> Settings</a></li>
            <li><a href="logout.php"><i class="fas fa-sign-out-alt"></i> Logout</a></li>
            </ul>
            <a href="theme_switch.php" class="theme-toggle" title="Toggle dark/light mode">
                <i class="fas fa-moon" data-icon="moon"></i>
                <i class="fas fa-sun" data-icon="sun" style="display:none;"></i>
            </a>
        </div>
    </nav>
    <main class="main-content">
