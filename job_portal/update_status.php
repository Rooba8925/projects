<?php
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    $application_id = intval($_POST['application_id']);
    $status = $_POST['status'];

    $stmt = $conn->prepare("UPDATE jobapplication SET status = ? WHERE application_id = ?");
    $stmt->bind_param("si", $status, $application_id);
    $stmt->execute();

    $stmt->close();
}

$conn->close();
header("Location: application_profiles.php");
exit;
