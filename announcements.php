<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'announcements';
require_once 'includes/header.php';

$pageTitle = 'Announcements & Schedules';
$pdo = getDB();

$showForm = isset($_GET['add']) || isset($_GET['edit']);
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editAnn = null;
if ($editId) {
    $st = $pdo->prepare('SELECT * FROM announcements WHERE id = ?');
    $st->execute([$editId]);
    $editAnn = $st->fetch(PDO::FETCH_ASSOC);
}

$announcements = $pdo->query('SELECT * FROM announcements ORDER BY schedule_date DESC, schedule_time DESC')->fetchAll(PDO::FETCH_ASSOC);
?>
<div class="page-header flex-between">
    <h1><i class="fas fa-bell"></i> Announcements & Schedules</h1>
    <a href="announcements.php?add=1" class="btn btn-primary"><i class="fas fa-plus"></i> Add Schedule</a>
</div>

<?php if ($showForm): ?>
<div class="card form-card">
    <h2><?php echo $editAnn ? 'Edit Schedule' : 'New Schedule / Date'; ?></h2>
    <form method="post" action="announcement_save.php">
        <?php if ($editAnn): ?><input type="hidden" name="id" value="<?php echo $editAnn['id']; ?>"><?php endif; ?>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editAnn['title'] ?? ''); ?>" placeholder="e.g. Dinner date, Beach trip">
        </div>
        <div class="form-group">
            <label for="content">Details (optional)</label>
            <textarea id="content" name="content" rows="3"><?php echo htmlspecialchars($editAnn['content'] ?? ''); ?></textarea>
        </div>
        <div class="form-row">
            <div class="form-group">
                <label for="schedule_date">Date</label>
                <input type="date" id="schedule_date" name="schedule_date" required value="<?php echo htmlspecialchars($editAnn['schedule_date'] ?? date('Y-m-d')); ?>">
            </div>
            <div class="form-group">
                <label for="schedule_time">Time (optional)</label>
                <input type="time" id="schedule_time" name="schedule_time" value="<?php echo htmlspecialchars($editAnn['schedule_time'] ?? ''); ?>">
            </div>
        </div>
        <div class="form-group">
            <label for="location">Location (optional)</label>
            <input type="text" id="location" name="location" value="<?php echo htmlspecialchars($editAnn['location'] ?? ''); ?>" placeholder="Where?">
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="announcements.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="announcement-list-page">
    <?php foreach ($announcements as $a): ?>
        <?php
            $isDone = !empty($a['is_read']);
            $statusLabel = $isDone ? '✓ Done' : 'Pending';
        ?>
        <div class="announcement-card <?php echo $a['schedule_date'] >= date('Y-m-d') ? 'upcoming' : ''; ?>">
            <div class="announcement-date">
                <span class="day"><?php echo date('j', strtotime($a['schedule_date'])); ?></span>
                <span class="month"><?php echo date('M', strtotime($a['schedule_date'])); ?></span>
            </div>
            <div class="announcement-body">
                <h3><?php echo htmlspecialchars($a['title']); ?></h3>
                <?php if ($a['content']): ?><p><?php echo nl2br(htmlspecialchars($a['content'])); ?></p><?php endif; ?>
                <?php if ($a['schedule_time']): ?><p class="time"><i class="fas fa-clock"></i> <?php echo date('g:i A', strtotime($a['schedule_time'])); ?></p><?php endif; ?>
                <?php if ($a['location']): ?><p class="location"><i class="fas fa-map-marker-alt"></i> <?php echo htmlspecialchars($a['location']); ?></p><?php endif; ?>
                <p class="announcement-status">
                    <span class="status-pill <?php echo $isDone ? 'status-done' : 'status-pending'; ?>">
                        <?php echo $statusLabel; ?>
                    </span>
                </p>
                <div class="announcement-actions">
                    <form method="post" action="announcement_status.php" class="status-form">
                        <input type="hidden" name="id" value="<?php echo $a['id']; ?>">
                        <button type="submit" class="btn btn-sm btn-outline">
                            <i class="fas fa-check-circle"></i> Update Status
                        </button>
                    </form>
                    <a href="announcements.php?edit=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                    <a href="announcement_delete.php?id=<?php echo $a['id']; ?>" class="btn btn-sm btn-outline delete-confirm" data-message="Delete this announcement?"><i class="fas fa-trash"></i></a>
                </div>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<?php if (empty($announcements) && !$showForm): ?>
    <div class="empty-state">
        <i class="fas fa-calendar-alt"></i>
        <p>No schedules yet. Add your first date or lakad!</p>
        <a href="announcements.php?add=1" class="btn btn-primary">Add Schedule</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
