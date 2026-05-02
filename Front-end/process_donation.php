<?php
session_start();
require_once __DIR__ . '/../API/config.php';

// Check if form was submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
    $amount = floatval($_POST['amount'] ?? 0);
    $name = $conn->real_escape_string(trim($_POST['name'] ?? ''));
    $email = $conn->real_escape_string(trim($_POST['email'] ?? ''));
    $phone = $conn->real_escape_string(trim($_POST['phone'] ?? ''));
    $message = $conn->real_escape_string(trim($_POST['message'] ?? ''));
    $payment_method = $conn->real_escape_string($_POST['payment_method'] ?? 'card');
    $lang_code = $_GET['lang'] ?? 'fr';
    
    // Translations for messages
    $t = [
        'en' => ['Thank you for your donation!', 'Invalid amount', 'Name and email required', 'Donation failed'],
        'sw' => ['Asante kwa mchango wako!', 'Kiasi batili', 'Jina na email zinahitajika', 'Mchango umeshindwa'],
        'fr' => ['Merci pour votre don!', 'Montant invalide', 'Nom et email requis', 'Échec du don']
    ];
    $msg = $t[$lang_code] ?? $t['fr'];
    
    // Validation
    if ($amount <= 0) {
        $_SESSION['donation_error'] = $msg[1];
        header("Location: donations.php?lang=" . urlencode($lang_code));
        exit;
    }
    
    if (empty($name) || empty($email)) {
        $_SESSION['donation_error'] = $msg[2];
        header("Location: donations.php?lang=" . urlencode($lang_code));
        exit;
    }
    
    // Create/drop and recreate donations table with correct schema
    $conn->query("DROP TABLE IF EXISTS donations");
    $conn->query("CREATE TABLE donations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        donor_name VARCHAR(255) NOT NULL,
        donor_email VARCHAR(255) NOT NULL,
        donor_phone VARCHAR(50),
        amount DECIMAL(10,2) NOT NULL,
        message TEXT,
        payment_method VARCHAR(50) DEFAULT 'card',
        transaction_id VARCHAR(255),
        status ENUM('pending', 'completed', 'failed', 'cancelled') DEFAULT 'pending',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    )");
    
    // Generate transaction ID
    $transaction_id = 'DON-' . strtoupper(uniqid());
    
    // Insert donation record
    $sql = "INSERT INTO donations (donor_name, donor_email, donor_phone, amount, message, payment_method, transaction_id, status) 
            VALUES ('$name', '$email', '$phone', $amount, '$message', '$payment_method', '$transaction_id', 'pending')";
    
    if ($conn->query($sql)) {
        $donation_id = $conn->insert_id;
        
        // In production, integrate with payment gateway here (Stripe, M-Pesa, etc.)
        // For now, simulate successful payment
        
        // Update status to completed (for demo)
        $conn->query("UPDATE donations SET status = 'completed' WHERE id = $donation_id");
        
        $_SESSION['donation_success'] = true;
        $_SESSION['donation_amount'] = $amount;
        $_SESSION['donation_id'] = $transaction_id;
        
        // Send thank you email
        $thank_you_subject = "Thank you for your donation!";
        $thank_you_body = "Dear $name,\n\nThank you for your generous donation of \$$amount to Masaka Initiative.\n\nTransaction ID: $transaction_id\n\nYour support helps us make a difference in the world.\n\nBest regards,\nMasaka Initiative Team";
        @mail($email, $thank_you_subject, $thank_you_body, "From: noreply@masaka.org");
        
    } else {
        $_SESSION['donation_error'] = $msg[3];
    }
    
    header("Location: donations.php?lang=" . urlencode($lang_code));
    exit;
}

// If not POST request, redirect to donations page
header("Location: donations.php");
exit;
?>
