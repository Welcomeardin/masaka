<?php
/**
 * Simple User Creator Tool
 * Access at: http://www.masaka.org/create_users.php
 */

$host = 'localhost';
$dbname = 'multi_lang_website';
$username = 'root';
$password = '';

$message = '';
$type = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
        $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

        $action = $_POST['action'] ?? '';

        if ($action === 'create_user') {
            $full_name = trim($_POST['full_name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password_input = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'user';

            if (empty($full_name) || empty($email) || empty($password_input)) {
                $message = 'Please fill in all fields';
                $type = 'error';
            } else {
                // Check if email already exists
                $check = $pdo->prepare("SELECT id FROM users WHERE email = ?");
                $check->execute([$email]);
                if ($check->fetch()) {
                    $message = 'Email already exists!';
                    $type = 'error';
                } else {
                    $hashed = password_hash($password_input, PASSWORD_BCRYPT);
                    $stmt = $pdo->prepare("INSERT INTO users (full_name, email, password, role, status) VALUES (?, ?, ?, ?, 'active')");
                    if ($stmt->execute([$full_name, $email, $hashed, $role])) {
                        $message = "✓ User '$full_name' created successfully! Email: $email | Password: $password_input";
                        $type = 'success';
                    } else {
                        $message = 'Error creating user';
                        $type = 'error';
                    }
                }
            }
        } elseif ($action === 'reset_password') {
            $email = trim($_POST['reset_email'] ?? '');
            $new_password = $_POST['reset_password'] ?? '';

            if (empty($email) || empty($new_password)) {
                $message = 'Please fill in email and new password';
                $type = 'error';
            } else {
                $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                $stmt = $pdo->prepare("UPDATE users SET password = ? WHERE email = ?");
                if ($stmt->execute([$hashed, $email])) {
                    if ($stmt->rowCount() > 0) {
                        $message = "✓ Password reset for '$email' | New Password: $new_password";
                        $type = 'success';
                    } else {
                        $message = 'Email not found in database';
                        $type = 'error';
                    }
                } else {
                    $message = 'Error resetting password';
                    $type = 'error';
                }
            }
        }
    } catch (PDOException $e) {
        $message = 'Database error: ' . $e->getMessage();
        $type = 'error';
    }
}

// Get all users
$users = [];
try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $stmt = $pdo->query("SELECT id, full_name, email, role, status FROM users ORDER BY created_at DESC");
    $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
} catch (Exception $e) {
    // Connection error
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Create Test Users - Masaka Admin</title>
    <link href="https://fonts.googleapis.com/icon?family=Material+Icons" rel="stylesheet">
    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, sans-serif;
            background: #f5f5f5;
            padding: 20px;
        }
        .container {
            max-width: 1200px;
            margin: 0 auto;
        }
        .header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 30px;
            border-radius: 10px;
            margin-bottom: 30px;
        }
        .header h1 { font-size: 28px; margin-bottom: 5px; }
        .header p { opacity: 0.9; }
        .grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin-bottom: 30px;
        }
        @media (max-width: 768px) {
            .grid { grid-template-columns: 1fr; }
        }
        .card {
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .card h2 {
            font-size: 18px;
            margin-bottom: 20px;
            color: #333;
            border-bottom: 2px solid #667eea;
            padding-bottom: 10px;
        }
        .form-group {
            margin-bottom: 15px;
        }
        label {
            display: block;
            margin-bottom: 5px;
            font-weight: 500;
            color: #333;
            font-size: 14px;
        }
        input, select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ddd;
            border-radius: 5px;
            font-size: 14px;
            font-family: inherit;
        }
        input:focus, select:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }
        button {
            width: 100%;
            padding: 10px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 5px;
            font-weight: 600;
            cursor: pointer;
            transition: background 0.3s;
        }
        button:hover { background: #5568d3; }
        .message {
            padding: 15px;
            border-radius: 5px;
            margin-bottom: 20px;
            border-left: 4px solid;
        }
        .success {
            background: #f0fdf4;
            color: #166534;
            border-color: #22c55e;
        }
        .error {
            background: #fef2f2;
            color: #b91c1c;
            border-color: #dc2626;
        }
        table {
            width: 100%;
            border-collapse: collapse;
            background: white;
            border-radius: 10px;
            overflow: hidden;
        }
        th {
            background: #667eea;
            color: white;
            padding: 12px;
            text-align: left;
            font-weight: 600;
        }
        td {
            padding: 12px;
            border-bottom: 1px solid #eee;
        }
        tr:last-child td { border-bottom: none; }
        .status-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
        }
        .status-active { background: #dcfce7; color: #166534; }
        .status-inactive { background: #fee2e2; color: #b91c1c; }
        .role-badge {
            display: inline-block;
            padding: 4px 12px;
            border-radius: 5px;
            font-size: 12px;
            font-weight: 600;
            background: #dbeafe;
            color: #1e40af;
        }
        .info-box {
            background: #eff6ff;
            border-left: 4px solid #3b82f6;
            padding: 15px;
            margin-bottom: 20px;
            border-radius: 5px;
            color: #1e40af;
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>🔐 Create Test Users</h1>
            <p>Manage admin user accounts for testing</p>
        </div>

        <?php if (!empty($message)): ?>
            <div class="message <?php echo $type; ?>">
                <?php echo htmlspecialchars($message); ?>
            </div>
        <?php endif; ?>

        <div class="grid">
            <!-- Create New User -->
            <div class="card">
                <h2>➕ Create New User</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="create_user">
                    
                    <div class="form-group">
                        <label for="full_name">Full Name *</label>
                        <input type="text" id="full_name" name="full_name" required placeholder="John Doe">
                    </div>

                    <div class="form-group">
                        <label for="email">Email Address *</label>
                        <input type="email" id="email" name="email" required placeholder="john@example.com">
                    </div>

                    <div class="form-group">
                        <label for="password">Password *</label>
                        <input type="text" id="password" name="password" required placeholder="test123456">
                        <small style="color: #666; font-size: 12px;">Min 8 characters recommended</small>
                    </div>

                    <div class="form-group">
                        <label for="role">Role *</label>
                        <select id="role" name="role" required>
                            <option value="user">User (Viewer)</option>
                            <option value="editor">Editor (Can edit content)</option>
                            <option value="admin">Admin (Full access)</option>
                        </select>
                    </div>

                    <button type="submit">Create User</button>
                </form>
            </div>

            <!-- Reset Password -->
            <div class="card">
                <h2>🔑 Reset Existing User Password</h2>
                <form method="POST">
                    <input type="hidden" name="action" value="reset_password">
                    
                    <div class="form-group">
                        <label for="reset_email">User Email *</label>
                        <input type="email" id="reset_email" name="reset_email" required placeholder="admin@example.com">
                    </div>

                    <div class="form-group">
                        <label for="reset_password">New Password *</label>
                        <input type="text" id="reset_password" name="reset_password" required placeholder="new_password_here">
                        <small style="color: #666; font-size: 12px;">Min 8 characters recommended</small>
                    </div>

                    <button type="submit">Reset Password</button>
                </form>
            </div>
        </div>

        <!-- Quick Setup Suggestions -->
        <div class="info-box">
            <strong>💡 Quick Setup:</strong><br>
            1. Reset admin@example.com password to "admin123"<br>
            2. Create a test user with email "test@example.com" and password "test123456"<br>
            3. Go to <strong>/auth/login.php</strong> and test login
        </div>

        <!-- All Users Table -->
        <div class="card">
            <h2>👥 All Users (<?php echo count($users); ?>)</h2>
            <?php if (count($users) > 0): ?>
                <table>
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($users as $user): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($user['full_name']); ?></td>
                                <td><?php echo htmlspecialchars($user['email']); ?></td>
                                <td><span class="role-badge"><?php echo ucfirst($user['role']); ?></span></td>
                                <td>
                                    <span class="status-badge status-<?php echo $user['status']; ?>">
                                        <?php echo ucfirst($user['status']); ?>
                                    </span>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p style="text-align: center; color: #666; padding: 20px;">No users found</p>
            <?php endif; ?>
        </div>

        <!-- Testing Instructions -->
        <div class="card" style="margin-top: 20px; background: #f0f4ff; border: 2px solid #667eea;">
            <h2 style="color: #667eea;">🧪 How to Test Login</h2>
            <ol style="margin-left: 20px; color: #333; line-height: 1.8;">
                <li><strong>Create a test user</strong> using the form above (or reset admin password)</li>
                <li><strong>Copy the email and password</strong> you entered</li>
                <li><strong>Go to:</strong> <code style="background: white; padding: 2px 6px; border-radius: 3px;">http://www.masaka.org/auth/login.php</code></li>
                <li><strong>Paste email and password</strong> in the login form</li>
                <li><strong>Click "Sign In"</strong> button</li>
                <li><strong>You should see the Admin Dashboard</strong> with Dashboard, Events, About, etc.</li>
                <li><strong>Test language switching:</strong> Click dropdown in top-right (English/Kiswahili/French)</li>
                <li><strong>Test logout:</strong> Click red "Logout" button</li>
            </ol>
        </div>
    </div>
</body>
</html>
