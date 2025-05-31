<?php
// Start session only if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

$servername = "127.0.0.1"; // Use IP instead of "localhost"
$username = "root"; // Default for XAMPP
$password = ""; // Default is empty for XAMPP
$dbname = "job_portal"; // Ensure this database exists
$port = 3306; // Change to 3306 if your MySQL uses that port

$conn = new mysqli($servername, $username, $password, $dbname, $port);

// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

// Debugging: Print session role (REMOVE after testing)
if (isset($_SESSION['role'])) {
    echo "Current role: " . $_SESSION['role'];
}
?>

