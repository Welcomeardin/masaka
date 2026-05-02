<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Redirect to login if not authenticated
if (empty($_SESSION['user_id'])) {
    $requested = $_SERVER['REQUEST_URI'] ?? '/';
    header('Location: /auth/login.php?redirect=' . urlencode($requested));
    exit;
}

// Check if user is active (prevent use of suspended accounts)
require_once __DIR__ . '/../API/config.php';
$user_id = (int)$_SESSION['user_id'];
$check = $conn->query("SELECT status FROM users WHERE id = $user_id");

if ($check && $check->num_rows > 0) {
    $user = $check->fetch_assoc();
    if ($user['status'] !== 'active') {
        session_destroy();
        header('Location: /auth/login.php?error=inactive');
        exit;
    }
} else {
    session_destroy();
    header('Location: /auth/login.php?error=invalid');
    exit;
}
