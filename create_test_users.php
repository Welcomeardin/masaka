<?php
/**
 * Test User Creator Script
 * Run this once to create test users with known passwords
 */

// Generate password hashes for testing
$testPassword = "test123456";
$adminPassword = "admin123";

$testHash = password_hash($testPassword, PASSWORD_BCRYPT);
$adminHash = password_hash($adminPassword, PASSWORD_BCRYPT);

echo "=== Test User Creator ===\n\n";

echo "Test User #1 - Regular User\n";
echo "Email: test@example.com\n";
echo "Password: test123456\n";
echo "Role: user\n";
echo "Hash: " . $testHash . "\n";
echo "SQL: INSERT INTO users (full_name, email, password, role, status) VALUES ('Test User', 'test@example.com', '$testHash', 'user', 'active');\n\n";

echo "Test User #2 - Editor\n";
echo "Email: editor@example.com\n";
echo "Password: test123456\n";
echo "Role: editor\n";
echo "Hash: " . $testHash . "\n";
echo "SQL: INSERT INTO users (full_name, email, password, role, status) VALUES ('Test Editor', 'editor@example.com', '$testHash', 'editor', 'active');\n\n";

echo "Test User #3 - Admin\n";
echo "Email: testadmin@example.com\n";
echo "Password: admin123\n";
echo "Role: admin\n";
echo "Hash: " . $adminHash . "\n";
echo "SQL: INSERT INTO users (full_name, email, password, role, status) VALUES ('Test Admin', 'testadmin@example.com', '$adminHash', 'admin', 'active');\n\n";

echo "Reset Existing Admin\n";
echo "Email: admin@example.com\n";
echo "New Password: admin123\n";
echo "Hash: " . $adminHash . "\n";
echo "SQL: UPDATE users SET password = '$adminHash' WHERE email='admin@example.com';\n\n";

echo "=== How to Use ===\n";
echo "1. Copy the SQL command above\n";
echo "2. Run it in phpMyAdmin or MySQL CLI\n";
echo "3. Then login with the credentials provided\n";
?>
