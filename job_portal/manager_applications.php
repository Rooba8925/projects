<?php
require 'config.php';
session_start();

// ✅ Ensure only employers can access this page
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'employer') {
    header("Location: login.php");
    exit();
}

$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// ✅ Fetch applications for this job
$stmt = $conn->prepare("SELECT * FROM job_applications WHERE job_id = ?");
$stmt->bind_param("i", $job_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <title>Manage Applications</title>
</head>
<body>
    <h1>Manage Applications</h1>
    <table border="1">
        <tr>
            <th>Applicant ID</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
        <?php
        while ($row = $result->fetch_assoc()) {
            echo "<tr>
                    <td>{$row['job_seeker_id']}</td>
                    <td>{$row['status']}</td>
                    <td>
                        <a href='update_status.php?id={$row['application_id']}&status=accepted'>Accept</a> | 
                        <a href='update_status.php?id={$row['application_id']}&status=rejected'>Reject</a> |
                        <a href='update_status.php?id={$row['application_id']}&status=pending'>Pending</a>
                    </td>
                  </tr>";
        }
        ?>
    </table>
</body>
</html>
