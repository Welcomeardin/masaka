<?php
// Debug/Test Script - Remove after testing
// Access at: /API/test_auth.php

header('Content-Type: text/html; charset=utf-8');

echo '<style>';
echo 'body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }';
echo '.box { background: white; padding: 20px; margin: 10px 0; border-radius: 5px; box-shadow: 0 1px 3px rgba(0,0,0,0.1); }';
echo '.success { color: green; font-weight: bold; }';
echo '.error { color: red; font-weight: bold; }';
echo '.warning { color: orange; font-weight: bold; }';
echo 'pre { background: #f0f0f0; padding: 10px; border-radius: 3px; overflow-x: auto; }';
echo 'table { width: 100%; border-collapse: collapse; }';
echo 'th, td { padding: 10px; text-align: left; border-bottom: 1px solid #ddd; }';
echo 'th { background: #667eea; color: white; }';
echo '</style>';

echo '<h1>🔍 Masaka Admin - Authentication Test</h1>';

// Test 1: Database Connection
echo '<div class="box">';
echo '<h2>Test 1: Database Connection</h2>';
require_once 'config.php';

if ($conn && $conn->connect_error) {
    echo '<p class="error">❌ Connection Error: ' . htmlspecialchars($conn->connect_error) . '</p>';
} elseif ($conn) {
    echo '<p class="success">✅ Connected to database</p>';
    echo '<p>Host: ' . htmlspecialchars($conn->server_info) . '</p>';
    echo '<p>Database: ' . htmlspecialchars(mysqli_get_host_info($conn)) . '</p>';
    
    // Test 2: Check Users Table
    echo '</div><div class="box">';
    echo '<h2>Test 2: Users Table Check</h2>';
    
    $result = $conn->query("SELECT id, full_name, email, role, status FROM users");
    
    if (!$result) {
        echo '<p class="error">❌ Error querying users: ' . htmlspecialchars($conn->error) . '</p>';
    } else {
        echo '<p class="success">✅ Users table accessible</p>';
        echo '<p>Found <strong>' . $result->num_rows . ' users</strong></p>';
        
        if ($result->num_rows > 0) {
            echo '<table>';
            echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th></tr>';
            while ($row = $result->fetch_assoc()) {
                echo '<tr>';
                echo '<td>' . htmlspecialchars($row['id']) . '</td>';
                echo '<td>' . htmlspecialchars($row['full_name']) . '</td>';
                echo '<td>' . htmlspecialchars($row['email']) . '</td>';
                echo '<td><strong>' . htmlspecialchars($row['role']) . '</strong></td>';
                echo '<td>' . (($row['status'] == 'active') ? '<span class="success">Active</span>' : '<span class="error">Inactive</span>') . '</td>';
                echo '</tr>';
            }
            echo '</table>';
        }
    }
    
    // Test 3: Password Verification
    echo '</div><div class="box">';
    echo '<h2>Test 3: Password Verification</h2>';
    
    $user_result = $conn->query("SELECT email, password FROM users WHERE role='admin' LIMIT 1");
    
    if ($user_result && $user_result->num_rows > 0) {
        $user = $user_result->fetch_assoc();
        echo '<p><strong>Testing with user:</strong> ' . htmlspecialchars($user['email']) . '</p>';
        echo '<p><strong>Password hash:</strong> ' . htmlspecialchars(substr($user['password'], 0, 30) . '...') . '</p>';
        
        // Try common passwords
        $test_passwords = ['Admin123', 'admin@123', 'password', 'masaka', '123456'];
        
        echo '<p><strong>Testing common passwords:</strong></p>';
        echo '<ul>';
        foreach ($test_passwords as $testPass) {
            $result = password_verify($testPass, $user['password']);
            $status = $result ? '<span class="success">✅ MATCH</span>' : '❌ No match';
            echo '<li>"' . htmlspecialchars($testPass) . '": ' . $status . '</li>';
        }
        echo '</ul>';
    } else {
        echo '<p class="warning">⚠️ No admin user found. Please create one first.</p>';
    }
    
    // Test 4: Session Test
    echo '</div><div class="box">';
    echo '<h2>Test 4: Session Test</h2>';
    
    if (session_status() === PHP_SESSION_ACTIVE) {
        echo '<p class="success">✅ Session is active</p>';
        echo '<p>Session ID: ' . htmlspecialchars(session_id()) . '</p>';
    } else {
        echo '<p class="warning">⚠️ Session not active yet</p>';
    }
    
    // Test 5: Quick Login Test
    echo '</div><div class="box">';
    echo '<h2>Test 5: Quick Login Test</h2>';
    echo '<p>Use this form to test login:</p>';
    echo '<form method="POST" action="auth_handler.php">';
    echo '<p>';
    echo '<label>Email: </label>';
    echo '<input type="email" name="email" value="admin@example.com" style="padding: 8px; margin: 0 10px;">';
    echo '</p>';
    echo '<p>';
    echo '<label>Password: </label>';
    echo '<input type="password" name="password" style="padding: 8px; margin: 0 10px;">';
    echo '</p>';
    echo '<input type="hidden" name="action" value="login">';
    echo '<button type="submit" style="padding: 8px 16px; background: #667eea; color: white; border: none; border-radius: 4px; cursor: pointer;">Test Login</button>';
    echo '</form>';
    
    // Test 6: Info
    echo '</div><div class="box">';
    echo '<h2>Test 6: System Info</h2>';
    echo '<p><strong>PHP Version:</strong> ' . phpversion() . '</p>';
    echo '<p><strong>MySQLi Version:</strong> ' . mysqli_get_client_version() . '</p>';
    echo '<p><strong>Current Time:</strong> ' . date('Y-m-d H:i:s') . '</p>';
    
} else {
    echo '<p class="error">❌ No database connection object</p>';
}

echo '</div>';

echo '<div class="box" style="margin-top: 30px; background: #fff3cd; border-left: 4px solid #ffc107;">';
echo '<h3>⚠️ Important: Remove this file after testing!</h3>';
echo '<p>Delete <code>/API/test_auth.php</code> from your server when you\'re done testing.</p>';
echo '</div>';
?>
