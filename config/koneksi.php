<?php
// Database Configuration
$host = "localhost";
$user = "root";
$pass = "";
$db   = "arsipmusik";

// Establish Database Connection
$conn = new mysqli($host, $user, $pass, $db);

// Check Connection
if ($conn->connect_error) {
    die("Koneksi database gagal: " . $conn->connect_error);
}

// Set Charset for UTF-8 Compatibility
$conn->set_charset("utf8mb4");
?>
