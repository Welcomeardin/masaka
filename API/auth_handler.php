<?php
session_start();
header('Content-Type: application/json');

// Database configuration
$host = 'localhost';
$dbname = 'multi_lang_website';
$username = 'root'; // Update with your DB username
$password = ''; // Update with your DB password

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo json_encode(['success' => false, 'message' => 'Database connection failed: ' . $e->getMessage()]);
    exit;
}

// Get request data
$action = $_POST['action'] ?? '';

if ($action === 'login') {
    $email = filter_var($_POST['email'] ?? '', FILTER_SANITIZE_EMAIL);
    $password = $_POST['password'] ?? '';
    $remember = isset($_POST['remember']) ? true : false;
    $redirect = $_POST['redirect'] ?? '/admin/';

    // Validate input
    if (empty($email) || empty($password)) {
        echo json_encode(['success' => false, 'message' => 'Please provide both email and password']);
        exit;
    }

    // Query user from database
    try {
        $stmt = $pdo->prepare("SELECT id, full_name, email, password, status, role FROM users WHERE email = ?");
        $stmt->execute([$email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        // Verify password
        if (!password_verify($password, $user['password'])) {
            echo json_encode(['success' => false, 'message' => 'Invalid email or password']);
            exit;
        }

        // Check account status
        if ($user['status'] !== 'active') {
            echo json_encode(['success' => false, 'message' => 'Your account is inactive. Please contact administrator']);
            exit;
        }

        // Login successful - set session variables
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['full_name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['user_role'] = $user['role'];
        $_SESSION['logged_in'] = true;

        // Set remember me cookie if requested
        if ($remember) {
            $token = bin2hex(random_bytes(32));
            $expiry = time() + (86400 * 30); // 30 days

            // Store token in database (you may need to add a remember_tokens table)
            // For now, just set cookie
            setcookie('remember_token', $token, $expiry, '/', '', false, true);
        }

        // Update last login time (optional - you may need to add last_login column to users table)
        // $updateStmt = $pdo->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
        // $updateStmt->execute([$user['id']]);

        echo json_encode([
            'success' => true,
            'message' => 'Login successful',
            'redirect' => $redirect,
            'user' => [
                'name' => $user['full_name'],
                'email' => $user['email'],
                'role' => $user['role']
            ]
        ]);
    } catch (PDOException $e) {
        echo json_encode(['success' => false, 'message' => 'Database error: ' . $e->getMessage()]);
    }
} elseif ($action === 'logout') {
    // Destroy session
    $_SESSION = array();

    if (isset($_COOKIE[session_name()])) {
        setcookie(session_name(), '', time() - 3600, '/');
    }

    session_destroy();

    // Clear remember me cookie
    setcookie('remember_token', '', time() - 3600, '/');

    echo json_encode(['success' => true, 'message' => 'Logged out successfully']);
} elseif ($action === 'check') {
    // Check if user is logged in
    if (!empty($_SESSION['user_id'])) {
        echo json_encode([
            'logged_in' => true,
            'user' => [
                'id' => $_SESSION['user_id'],
                'name' => $_SESSION['user_name'] ?? '',
                'email' => $_SESSION['user_email'] ?? '',
                'role' => $_SESSION['user_role'] ?? ''
            ]
        ]);
    } else {
        echo json_encode(['logged_in' => false]);
    }
} else {
    echo json_encode(['success' => false, 'message' => 'Invalid action']);
}
