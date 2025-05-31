<?php
$host = "127.0.0.1"; 
$username = "root"; 
$password = ""; 
$database = "job_portal"; 
$port = 3306; // Updated port number

// ✅ Correct way to initialize MySQLi connection
$conn = new mysqli($host, $username, $password, $database, $port);

// ✅ Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>
