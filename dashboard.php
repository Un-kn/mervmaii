<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'dashboard';
require_once 'includes/header.php';

$pageTitle = 'Dashboard';

$pdo = getDB();
$userId = (int)$_SESSION['user_id'];

// Load theme
$st = $pdo->prepare('SELECT theme FROM user_settings WHERE user_id = ?');
$st->execute([$userId]);
$row = $st->fetch(PDO::FETCH_ASSOC);
$_SESSION['theme'] = $row ? $row['theme'] : 'light';

// Stats
$photoCount = $pdo->query("SELECT COUNT(*) FROM photos")->fetchColumn();
$albumCount = $pdo->query("SELECT COUNT(*) FROM albums")->fetchColumn();
$noteCount = $pdo->query("SELECT COUNT(*) FROM love_notes")->fetchColumn();

$st = $pdo->prepare('SELECT anniversary_date FROM users WHERE id = ?');
$st->execute([$userId]);
$anniversary = $st->fetchColumn();
$monthsTogether = 0;
if ($anniversary) {
    $start = new DateTime($anniversary);
    $now = new DateTime();
    $monthsTogether = $start->diff($now)->y * 12 + $start->diff($now)->m;
}

// Upcoming announcements
$upcoming = $pdo->query("SELECT id, title, schedule_date, schedule_time FROM announcements WHERE schedule_date >= CURDATE() ORDER BY schedule_date ASC, schedule_time ASC LIMIT 5")->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header">
    <h1><i class="fas fa-home"></i> Dashboard</h1>
    <p class="welcome-text">Welcome back, <?php echo htmlspecialchars($_SESSION['display_name'] ?? 'Mervmaii'); ?>!</p>
</div>

<div class="dashboard-cards">
    <div class="stat-card animate-slide-up" style="animation-delay: 0.1s">
        <div class="stat-icon purple"><i class="fas fa-heart"></i></div>
        <div class="stat-content">
            <span class="stat-value"><?php echo (int)$monthsTogether; ?></span>
            <span class="stat-label">Months Together</span>
        </div>
    </div>
    <div class="stat-card animate-slide-up" style="animation-delay: 0.2s">
        <div class="stat-icon purple"><i class="fas fa-images"></i></div>
        <div class="stat-content">
            <span class="stat-value"><?php echo (int)$photoCount; ?></span>
            <span class="stat-label">Photos</span>
        </div>
    </div>
    <div class="stat-card animate-slide-up" style="animation-delay: 0.3s">
        <div class="stat-icon purple"><i class="fas fa-folder"></i></div>
        <div class="stat-content">
            <span class="stat-value"><?php echo (int)$albumCount; ?></span>
            <span class="stat-label">Albums</span>
        </div>
    </div>
    <div class="stat-card animate-slide-up" style="animation-delay: 0.4s">
        <div class="stat-icon purple"><i class="fas fa-sticky-note"></i></div>
        <div class="stat-content">
            <span class="stat-value"><?php echo (int)$noteCount; ?></span>
            <span class="stat-label">Love Notes</span>
        </div>
    </div>
</div>

<div class="dashboard-grid">
    <section class="card animate-fade-in">
        <h2><i class="fas fa-bell"></i> Upcoming Dates & Schedules</h2>
        <?php if (empty($upcoming)): ?>
            <p class="muted">No upcoming schedules. <a href="announcements.php">Add one</a>!</p>
        <?php else: ?>
            <ul class="announcement-list">
                <?php foreach ($upcoming as $a): ?>
                    <li>
                        <strong><?php echo htmlspecialchars($a['title']); ?></strong>
                        <span class="date"><?php echo date('M j, Y', strtotime($a['schedule_date'])); ?>
                            <?php if (!empty($a['schedule_time'])) echo ' · ' . date('g:i A', strtotime($a['schedule_time'])); ?>
                        </span>
                    </li>
                <?php endforeach; ?>
            </ul>
            <a href="announcements.php" class="btn btn-outline btn-sm">View all</a>
        <?php endif; ?>
    </section>
    <section class="card animate-fade-in">
        <h2><i class="fas fa-quote-left"></i> Quick Links</h2>
        <div class="quick-links">
            <a href="album_add.php" class="quick-link"><i class="fas fa-plus-circle"></i> Add Album</a>
            <a href="notes.php?add=1" class="quick-link"><i class="fas fa-pen"></i> Write Love Note</a>
            <a href="announcements.php?add=1" class="quick-link"><i class="fas fa-calendar-plus"></i> Add Schedule</a>
        </div>
    </section>
</div>
<?php require_once 'includes/footer.php'; ?>
