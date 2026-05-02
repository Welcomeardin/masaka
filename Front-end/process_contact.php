<?php
session_start();
require_once __DIR__ . '/../API/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get and sanitize form data
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $subject = $conn->real_escape_string(trim($_POST['subject'] ?? ''));
    $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    $lang_code = $_GET['lang'] ?? 'fr';
    
    // Validation
    $errors = [];
    if (empty($name)) {
        $errors[] = 'Name is required';
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }
    if (empty($subject)) {
        $errors[] = 'Subject is required';
    }
    if (empty($message)) {
        $errors[] = 'Message is required';
    }
    
    if (!empty($errors)) {
        $_SESSION['contact_errors'] = $errors;
        header("Location: contact.php?lang=" . urlencode($lang_code));
        exit;
    }
    
    // Create/drop and recreate contact_messages table with correct schema
    $conn->query("DROP TABLE IF EXISTS contact_messages");
    $conn->query("CREATE TABLE contact_messages (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(255) NOT NULL,
        email VARCHAR(255) NOT NULL,
        phone VARCHAR(50),
        subject VARCHAR(255) NOT NULL,
        message TEXT NOT NULL,
        status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Insert into database
    $sql = "INSERT INTO contact_messages (name, email, subject, message, status) 
            VALUES ('$name', '$email', '$subject', '$message', 'new')";
    
    if ($conn->query($sql)) {
        $_SESSION['contact_success'] = true;
        
        // Try to send email notification (optional)
        $settings_query = $conn->query("SELECT email FROM settings LIMIT 1");
        $settings = $settings_query->fetch_assoc();
        $admin_email = $settings['email'] ?? '';
        
        if (!empty($admin_email)) {
            $to = $admin_email;
            $email_subject = "New Contact Message: $subject";
            $email_body = "Name: $name\nEmail: $email\nSubject: $subject\n\nMessage:\n$message";
            $headers = "From: $email\r\nReply-To: $email";
            @mail($to, $email_subject, $email_body, $headers);
        }
    } else {
        $_SESSION['contact_errors'] = ['Failed to save message. Please try again.'];
    }
    
    header("Location: contact.php?lang=" . urlencode($lang_code));
    exit;
}

// If not POST request, redirect to contact page
header("Location: contact.php");
exit;
?>
