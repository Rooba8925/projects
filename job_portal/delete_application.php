<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== "job seeker") {
    exit("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

$sql = "
    SELECT ja.application_id, ja.status, ja.applied_date, jl.title AS job_title, ep.company, u.name
    FROM jobapplication ja
    JOIN jobseeker_profile jp ON ja.candidate_id = jp.candidate_id
    JOIN job_listings jl ON ja.job_id = jl.job_id
    JOIN employerprofile ep ON jl.employer_id = ep.employer_id
    JOIN users u ON jp.user_id = u.user_id
    WHERE jp.user_id = ?
    ORDER BY ja.applied_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>My Applications</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            padding: 40px;
            background-color: #e3f2fd;
        }

        h2 {
            text-align: center;
            color: #0d47a1;
        }

        table {
            width: 90%;
            margin: 30px auto;
            border-collapse: collapse;
            background-color: white;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }

        th, td {
            padding: 15px;
            border-bottom: 1px solid #ccc;
            text-align: left;
        }

        th {
            background-color: #1976d2;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .delete-link {
            color: #d32f2f;
            text-decoration: none;
            font-weight: bold;
        }

        .delete-link:hover {
            text-decoration: underline;
        }

        .success-msg {
            color: green;
            text-align: center;
            font-weight: bold;
            margin-top: 20px;
            background: #c8e6c9;
            padding: 10px;
            border: 1px solid #81c784;
            border-radius: 5px;
        }
    </style>
</head>
<body>

<h2>My Job Applications</h2>

<?php
if (isset($_GET['deleted'])) {
    echo "<p class='success-msg'>Application deleted successfully!</p>";
}
?>

<table>
    <tr>
        <th>Candidate Name</th>
        <th>Company</th>
        <th>Job Title</th>
        <th>Applied Date</th>
        <th>Status</th>
        <th>Action</th>
    </tr>

    <?php while ($row = $result->fetch_assoc()) { ?>
        <tr>
            <td><?php echo htmlspecialchars($row['name']); ?></td>
            <td><?php echo htmlspecialchars($row['company']); ?></td>
            <td><?php echo htmlspecialchars($row['job_title']); ?></td>
            <td><?php echo htmlspecialchars($row['applied_date']); ?></td>
            <td><?php echo htmlspecialchars($row['status']); ?></td>
            <td>
                <a href="delete_application.php?app_id=<?php echo $row['application_id']; ?>"
                   class="delete-link"
                   onclick="return confirm('Are you sure you want to delete this application?');">
                   🗑️ Delete
                </a>
            </td>
        </tr>
    <?php } ?>
</table>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>
