<?php
$host = "localhost";
$user = "root";
$pass = "";
$dbname = "multi_lang_website";

$conn = new mysqli($host, $user, $pass, $dbname);

// Note: Connection errors are handled by the calling files
// to ensure proper JSON responses for AJAX requests

// Set charset to utf8mb4
if (!$conn->connect_error) {
    $conn->set_charset("utf8mb4");
}
