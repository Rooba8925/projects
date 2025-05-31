<?php
session_start();
include 'db.php'; // Database connection

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $employer_id = $_POST['employer_id'];
    $company = $_POST['company'];
    $job_title = trim($_POST['job_title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);
    $category = trim($_POST['category']); // ✅ Accepts text input now

    if (empty($job_title) || empty($description) || empty($location) || empty($salary) || empty($category)) {
        exit("Error: All fields are required.");
    }

    // ✅ Insert job posting into the database
    $query = "INSERT INTO job_listings (employer_id, company, job_title, description, location, salary, category) VALUES (?, ?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: Query preparation failed - " . $conn->error);
    }

    $stmt->bind_param("issssds", $employer_id, $company, $job_title, $description, $location, $salary, $category);
    
    if ($stmt->execute()) {
        echo "Success: Job posted successfully!";
        header("Location: employer_dashboard.php"); // Redirect back to the dashboard
        exit;
    } else {
        echo "Error: " . $stmt->error;
    }

    $stmt->close();
    $conn->close();
}
?>
