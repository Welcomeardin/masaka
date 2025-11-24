<?php
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

if (empty($_SESSION['user_id'])) {
    $requested = $_SERVER['REQUEST_URI'] ?? '/';

    $docRoot = rtrim(str_replace('\\', '/', $_SERVER['DOCUMENT_ROOT']), '/');
    $authDir = str_replace('\\', '/', __DIR__);
    $loginUrl = '/auth/login.php';
    if (strpos($authDir, $docRoot) === 0) {
        $loginUrl = substr($authDir, strlen($docRoot));
        if ($loginUrl === '') $loginUrl = '/auth';
        $loginUrl = rtrim($loginUrl, '/') . '/login.php';
    }

    $sep = (strpos($loginUrl, '?') === false) ? '?' : '&';
    header('Location: ' . $loginUrl . $sep . 'redirect=' . urlencode($requested));
    exit;
}
