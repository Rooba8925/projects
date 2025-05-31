<?php
require 'config.php';
session_start();

// ✅ Ensure only employers can post jobs
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login.php");
    exit();
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $title = trim($_POST['title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);
    $category = trim($_POST['category']);
    $employer_id = $_SESSION['user_id'];

    // ✅ Insert job into database
    $stmt = $conn->prepare("INSERT INTO jobs (employer_id, title, description, location, salary, category) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("isssis", $employer_id, $title, $description, $location, $salary, $category);

    if ($stmt->execute()) {
        header("Location: employer_dashboard.php");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>

