<?php
// Include settings
require_once __DIR__ . '/settings.php';

$host = "localhost";
$username = "root";
$password = "";
$database = "restaurant_db";

$conn = new mysqli($host, $username, $password, $database);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Only start session if one hasn't been started already
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?> 