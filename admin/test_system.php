<?php
/**
 * Admin System Test
 * Run this to verify database connectivity and upload folder permissions
 */

require_once __DIR__ . '/../API/config.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html>
<head>
    <title>Admin System Test</title>
    <style>
        body { font-family: Arial, sans-serif; padding: 20px; max-width: 900px; margin: 0 auto; }
        .test-section { background: #f5f5f5; padding: 15px; margin: 15px 0; border-radius: 8px; }
        .success { color: green; font-weight: bold; }
        .error { color: red; font-weight: bold; }
        .warning { color: orange; font-weight: bold; }
        pre { background: #333; color: #0f0; padding: 10px; overflow-x: auto; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { padding: 8px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background: #333; color: white; }
    </style>
</head>
<body>
    <h1>Admin System Test Report</h1>
    <p>Generated: <?php echo date('Y-m-d H:i:s'); ?></p>

    <div class="test-section">
        <h2>1. Database Connection</h2>
        <?php
        if ($conn->connect_error) {
            echo '<p class="error">Connection FAILED: ' . $conn->connect_error . '</p>';
        } else {
            echo '<p class="success">Database connection: OK</p>';
            echo '<p>Server version: ' . $conn->server_info . '</p>';
        }
        ?>
    </div>

    <div class="test-section">
        <h2>2. Upload Directories</h2>
        <?php
        $dirs = [
            '../uploads/team/' => 'Team images',
            '../uploads/slides/' => 'Slide images',
            '../uploads/events/' => 'Event images',
            '../uploads/gallery/' => 'Gallery images',
            '../uploads/about/' => 'About images',
            '../uploads/settings/' => 'Settings/Logo images',
        ];
        
        foreach ($dirs as $dir => $name) {
            $fullPath = __DIR__ . '/' . $dir;
            echo "<h3>$name ($dir)</h3>";
            
            if (!is_dir($fullPath)) {
                echo '<p class="warning">Directory does not exist. Attempting to create...</p>';
                if (@mkdir($fullPath, 0755, true)) {
                    echo '<p class="success">Directory created successfully!</p>';
                } else {
                    echo '<p class="error">Failed to create directory. Check parent folder permissions.</p>';
                }
            } else {
                echo '<p class="success">Directory exists</p>';
            }
            
            if (is_dir($fullPath)) {
                if (is_writable($fullPath)) {
                    echo '<p class="success">Directory is writable</p>';
                } else {
                    echo '<p class="error">Directory is NOT writable!</p>';
                    echo '<p>Attempting to fix permissions...</p>';
                    if (@chmod($fullPath, 0755)) {
                        echo '<p class="success">Permissions fixed!</p>';
                    } else {
                        echo '<p class="error">Could not fix permissions.</p>';
                    }
                }
            }
            echo '<hr>';
        }
        ?>
    </div>

    <div class="test-section">
        <h2>3. Database Tables Check</h2>
        <?php
        $tables = ['team', 'team_translations', 'slides', 'events', 'about', 'gallery_items', 'gallery_items_translations', 'settings', 'settings_translations', 'languages'];
        
        echo '<table>';
        echo '<tr><th>Table</th><th>Status</th><th>Rows</th></tr>';
        
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            $exists = $result && $result->num_rows > 0;
            
            if ($exists) {
                $countResult = $conn->query("SELECT COUNT(*) as cnt FROM $table");
                $count = $countResult ? $countResult->fetch_assoc()['cnt'] : 'N/A';
                echo "<tr><td>$table</td><td class='success'>EXISTS</td><td>$count rows</td></tr>";
            } else {
                echo "<tr><td>$table</td><td class='error'>MISSING</td><td>-</td></tr>";
            }
        }
        echo '</table>';
        ?>
    </div>

    <div class="test-section">
        <h2>4. Test Team Insert (Simulated)</h2>
        <?php
        $testQuery = "INSERT INTO team (name, role, bio, sort_order, status, created_at) 
                      VALUES ('__TEST_USER__', 'Test Role', 'Test bio', 999, 'inactive', NOW())";
        
        if ($conn->query($testQuery)) {
            $testId = $conn->insert_id;
            echo '<p class="success">Test INSERT successful! ID: ' . $testId . '</p>';
            
            // Clean up
            $conn->query("DELETE FROM team WHERE id = $testId");
            echo '<p class="success">Test data cleaned up</p>';
        } else {
            echo '<p class="error">Test INSERT failed: ' . $conn->error . '</p>';
        }
        ?>
    </div>

    <div class="test-section">
        <h2>5. PHP Configuration</h2>
        <ul>
            <li>PHP Version: <?php echo phpversion(); ?></li>
            <li>max_upload_size: <?php echo ini_get('upload_max_filesize'); ?></li>
            <li>post_max_size: <?php echo ini_get('post_max_size'); ?></li>
            <li>max_execution_time: <?php echo ini_get('max_execution_time'); ?>s</li>
            <li>memory_limit: <?php echo ini_get('memory_limit'); ?></li>
            <li>file_uploads: <?php echo ini_get('file_uploads') ? 'Enabled' : 'Disabled'; ?></li>
        </ul>
    </div>

    <div class="test-section">
        <h2>6. File Permissions Test</h2>
        <?php
        $testFile = __DIR__ . '/../uploads/test_write_' . time() . '.txt';
        if (@file_put_contents($testFile, 'Test content')) {
            echo '<p class="success">Can write to uploads directory</p>';
            @unlink($testFile);
        } else {
            echo '<p class="error">Cannot write to uploads directory</p>';
        }
        ?>
    </div>

    <div style="margin-top: 30px; padding: 15px; background: #e3f2fd; border-radius: 8px;">
        <h3>Next Steps</h3>
        <p>If all tests pass, try adding a team member through the admin panel. If there are errors above, fix them first.</p>
        <p><a href="team.php" style="display: inline-block; padding: 10px 20px; background: #4CAF50; color: white; text-decoration: none; border-radius: 4px;">Go to Team Page</a></p>
    </div>

</body>
</html>
