<?php
// Password Reset Utility
// Access at: /API/reset_password.php
// IMPORTANT: Delete this file after use for security!

header('Content-Type: text/html; charset=utf-8');

echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 40px auto; max-width: 600px; background: #f5f5f5; }';
echo '.box { background: white; padding: 30px; border-radius: 8px; box-shadow: 0 2px 8px rgba(0,0,0,0.1); }';
echo '.success { color: green; background: #d4edda; padding: 15px; border-radius: 5px; margin: 20px 0; }';
echo '.error { color: #d32f2f; background: #ffebee; padding: 15px; border-radius: 5px; margin: 20px 0; }';
echo '.warning { color: #f57c00; background: #fff3e0; padding: 15px; border-radius: 5px; margin: 20px 0; }';
echo 'input, button { padding: 10px; margin: 10px 0; width: 100%; font-size: 14px; border: 1px solid #ddd; border-radius: 4px; }';
echo 'button { background: #667eea; color: white; cursor: pointer; border: none; font-weight: bold; }';
echo 'button:hover { background: #764ba2; }';
echo 'pre { background: #f5f5f5; padding: 15px; border-radius: 4px; overflow-x: auto; word-break: break-all; }';
echo '.copy-btn { width: auto; padding: 5px 10px; margin-left: 5px; background: #4CAF50; }';
echo 'h1 { color: #333; }';
echo 'p { line-height: 1.6; color: #666; }';
echo '</style>';

echo '<div class="box">';
echo '<h1>🔐 Password Reset Utility</h1>';

require_once 'config.php';

if (!$conn) {
    echo '<div class="error">❌ Database connection failed</div>';
    exit;
}

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $new_password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    if (empty($new_password)) {
        echo '<div class="error">❌ Password cannot be empty</div>';
    } elseif ($new_password !== $confirm_password) {
        echo '<div class="error">❌ Passwords do not match</div>';
    } elseif (strlen($new_password) < 6) {
        echo '<div class="error">❌ Password must be at least 6 characters</div>';
    } else {
        // Hash the password
        $hash = password_hash($new_password, PASSWORD_BCRYPT);
        
        // Update admin user
        $query = "UPDATE users SET password = '" . $conn->real_escape_string($hash) . "' WHERE role='admin' LIMIT 1";
        
        if ($conn->query($query)) {
            echo '<div class="success">';
            echo '<h2>✅ Password Updated Successfully!</h2>';
            echo '<p><strong>New Password:</strong> ' . htmlspecialchars($new_password) . '</p>';
            echo '<p><strong>You can now login with:</strong></p>';
            echo '<p><strong>Email:</strong> admin@example.com</p>';
            echo '<p><strong>Password:</strong> ' . htmlspecialchars($new_password) . '</p>';
            echo '<p><a href="../../auth/login.php" style="color: #667eea;">Go to Login Page →</a></p>';
            echo '</div>';
        } else {
            echo '<div class="error">❌ Error updating password: ' . htmlspecialchars($conn->error) . '</div>';
        }
    }
}

// Show form
echo '<h2>Set New Admin Password</h2>';
echo '<form method="POST">';

echo '<label><strong>New Password:</strong></label>';
echo '<input type="password" name="password" required placeholder="Enter new password (min 6 characters)">';

echo '<label><strong>Confirm Password:</strong></label>';
echo '<input type="password" name="confirm_password" required placeholder="Re-enter password">';

echo '<button type="submit">Update Password</button>';

echo '</form>';

echo '<hr style="margin: 40px 0; border: 1px solid #ddd;">';

// Show current users
echo '<h2>Current Admin Users</h2>';

$users = $conn->query("SELECT id, full_name, email, role, status FROM users WHERE role='admin'");

if ($users && $users->num_rows > 0) {
    echo '<p><strong>Admin accounts found:</strong></p>';
    while ($user = $users->fetch_assoc()) {
        echo '<p style="padding: 10px; background: #f5f5f5; border-radius: 4px;">';
        echo '<strong>' . htmlspecialchars($user['full_name']) . '</strong><br>';
        echo 'Email: ' . htmlspecialchars($user['email']) . '<br>';
        echo 'Status: <strong>' . ($user['status'] == 'active' ? '✅ Active' : '❌ Inactive') . '</strong>';
        echo '</p>';
    }
} else {
    echo '<p><div class="error">❌ No admin users found. Create one first.</div></p>';
}

echo '<hr style="margin: 40px 0; border: 1px solid #ddd;">';

echo '<div class="warning">';
echo '<h3>⚠️ SECURITY WARNING</h3>';
echo '<p><strong>IMPORTANT:</strong> Delete this file (<code>/API/reset_password.php</code>) immediately after use!</p>';
echo '<p>Anyone with access to this file can reset admin passwords.</p>';
echo '</div>';

echo '</div>';
?>
