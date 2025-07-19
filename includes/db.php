<?php
// Database connection settings (MAMP defaults)
$host = 'localhost';
$user = 'root';
$password = 'root';
$database = 'mini_ecommerce';
$port = 3306; // Update if your MySQL runs on a different port

$conn = new mysqli($host, $user, $password, $database, $port);
if ($conn->connect_error) {
    die('Database connection failed: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
?> 