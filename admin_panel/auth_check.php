<?php
session_start();

// Check if user is logged in
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    // Redirect to login page
    header('Location: login.php');
    exit;
}

// Optional: Check session timeout (30 minutes)
if (isset($_SESSION['login_time']) && (time() - $_SESSION['login_time']) > 1800) {
    session_destroy();
    header('Location: login.php?timeout=1');
    exit;
}

// Update last activity time
$_SESSION['login_time'] = time();
?>
