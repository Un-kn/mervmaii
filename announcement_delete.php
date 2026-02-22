<?php
require_once 'config/init.php';
require_once 'includes/auth.php';

$id = (int)($_GET['id'] ?? 0);
if ($id) getDB()->prepare('DELETE FROM announcements WHERE id = ?')->execute([$id]);
header('Location: announcements.php');
exit;
