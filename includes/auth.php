<?php
if (!isset($_SESSION['user_id'])) {
    header('Location: index.php');
    exit;
}
// Track user activity
updateUserActivity($_SESSION['user_id']);