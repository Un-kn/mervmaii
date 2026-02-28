<?php
require_once 'config/init.php';
require_once 'includes/auth.php';
$page = 'notes';
require_once 'includes/header.php';

$pageTitle = 'Love Notes';
$pdo = getDB();

$authorFilter = isset($_GET['author']) ? trim($_GET['author']) : '';
if ($authorFilter !== '') {
    $st = $pdo->prepare('SELECT * FROM love_notes WHERE author_name = ? ORDER BY updated_at DESC');
    $st->execute([$authorFilter]);
    $notes = $st->fetchAll(PDO::FETCH_ASSOC);
} else {
    $notes = $pdo->query('SELECT * FROM love_notes ORDER BY updated_at DESC')->fetchAll(PDO::FETCH_ASSOC);
}

// Preload reactions for all notes
$noteReactions = [];
if (!empty($notes)) {
    $noteIds = array_column($notes, 'id');
    $placeholders = implode(',', array_fill(0, count($noteIds), '?'));
    $st = $pdo->prepare("SELECT note_id, reactor, reaction_type FROM love_note_reactions WHERE note_id IN ($placeholders)");
    $st->execute($noteIds);
    while ($r = $st->fetch(PDO::FETCH_ASSOC)) {
        $nid = (int)$r['note_id'];
        if (!isset($noteReactions[$nid])) {
            $noteReactions[$nid] = [];
        }
        $noteReactions[$nid][] = $r;
    }
}
$showForm = isset($_GET['add']) || isset($_GET['edit']);
$editId = isset($_GET['edit']) ? (int)$_GET['edit'] : 0;
$editNote = null;
if ($editId) {
    $st = $pdo->prepare('SELECT * FROM love_notes WHERE id = ?');
    $st->execute([$editId]);
    $editNote = $st->fetch(PDO::FETCH_ASSOC);
}
?>
<div class="page-header flex-between">
    <h1><i class="fas fa-sticky-note"></i> Love Notes</h1>
    <div class="page-header-actions">
        <div class="note-filter-buttons">
            <?php
            $activeAuthor = $authorFilter;
            $authors = [
                '' => 'All',
                'Mervin Parinas' => 'Mervin',
                'Rhea Mae Magallanes' => 'Rhea',
            ];
            foreach ($authors as $full => $label):
                $isActive = ($activeAuthor === $full);
            ?>
                <a href="notes.php<?php echo $full !== '' ? '?author=' . urlencode($full) : ''; ?>" class="btn btn-sm <?php echo $isActive ? 'btn-primary' : 'btn-outline'; ?>">
                    <?php echo htmlspecialchars($label); ?>
                </a>
            <?php endforeach; ?>
        </div>
        <a href="notes.php?add=1" class="btn btn-primary"><i class="fas fa-plus"></i> Add Note</a>
    </div>
</div>

<?php if ($showForm): ?>
<div class="card form-card">
    <h2><?php echo $editNote ? 'Edit Note' : 'New Love Note'; ?></h2>
    <form method="post" action="note_save.php">
        <?php if ($editNote): ?><input type="hidden" name="id" value="<?php echo $editNote['id']; ?>"><?php endif; ?>
        <div class="form-group">
            <label for="title">Title</label>
            <input type="text" id="title" name="title" required value="<?php echo htmlspecialchars($editNote['title'] ?? ''); ?>">
        </div>
        <div class="form-group">
            <label for="content">Content</label>
            <textarea id="content" name="content" rows="6" required><?php echo htmlspecialchars($editNote['content'] ?? ''); ?></textarea>
        </div>
        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save</button>
        <a href="notes.php" class="btn btn-outline">Cancel</a>
    </form>
</div>
<?php endif; ?>

<div class="notes-grid">
    <?php foreach ($notes as $n): ?>
        <article class="note-card animate-fade-in">
            <h3><?php echo htmlspecialchars($n['title']); ?></h3>
            <?php $plainContent = $n['content'] ?? ''; ?>
            <p class="note-content note-preview"><?php echo nl2br(htmlspecialchars($plainContent)); ?></p>
            <a href="view_whole_notes.php?id=<?php echo $n['id']; ?>" class="note-view-link">View</a><br>
            <div class="note-meta">
                <div class="note-info">
                    <span>
                        <i class="fas fa-user"></i>
                        <?php
                            $currentUser = trim($_SESSION['display_name'] ?? ($_SESSION['username'] ?? ''));
                            $authorDisplay = $n['author_name'] ?: 'Unknown';
                            if ($authorDisplay === $currentUser && $currentUser !== '') {
                                $authorDisplay = 'You';
                            }
                        ?>
                        <?php echo htmlspecialchars($authorDisplay); ?>
                    </span>
                    <span>
                        <i class="fas fa-clock"></i>
                        <?php echo date('M j, Y g:i A', strtotime($n['created_at'])); ?>
                    </span>
                </div>
                <a href="notes.php?edit=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline"><i class="fas fa-edit"></i></a>
                <a href="note_delete.php?id=<?php echo $n['id']; ?>" class="btn btn-sm btn-outline delete-confirm" data-message="Delete this note?"><i class="fas fa-trash"></i></a>
            </div>
            <br>
            <?php
                $nid = (int)$n['id'];
                $reactions = $noteReactions[$nid] ?? [];
                $currentUser = trim($_SESSION['display_name'] ?? ($_SESSION['username'] ?? ''));
                $userReaction = null;
                $counts = ['like' => 0, 'heart' => 0, 'care' => 0, 'wow' => 0, 'sad' => 0, 'angry' => 0, 'hahaha' => 0];
                $namesByType = ['like' => [], 'heart' => [], 'care' => [], 'wow' => [], 'sad' => [], 'angry' => [], 'hahaha' => []];
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
                    'hahaha' => '😂',
                ];
                $mainLabel = $userReaction ? ucfirst($userReaction) : 'Like';
                $mainEmoji = $userReaction ? $reactionEmoji[$userReaction] : '👍';
            ?>
            <div class="reaction-container" data-reaction-context="note" data-id="<?php echo $nid; ?>">
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
                            $filteredNames = array_diff(array_unique($names), [$currentUser]);
                            if (!empty($filteredNames)) {
                                $summaryParts[] = $reactionEmoji[$type] . ' ' . implode(', ', $filteredNames);
                            }
                        }
                ?>
                    <div class="reaction-summary">
                        <span class="reaction-count"><?php echo $totalReactions; ?> reaction<?php echo $totalReactions > 1 ? 's' : ''; ?></span>
                        <span class="reaction-names"><?php echo htmlspecialchars(implode(' • ', $summaryParts)); ?></span>
                    </div>
                <?php endif; ?>
            </div>
        </article>
    <?php endforeach; ?>
</div>

<?php if (empty($notes) && !$showForm): ?>
    <div class="empty-state">
        <i class="fas fa-heart"></i>
        <p>No love notes yet. Write your first one!</p>
        <a href="notes.php?add=1" class="btn btn-primary">Add Love Note</a>
    </div>
<?php endif; ?>

<?php require_once 'includes/footer.php'; ?>
