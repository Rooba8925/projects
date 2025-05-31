<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'job seeker') {
    exit("Error: Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Handle Clear application
if (isset($_GET['clear_app'])) {
    $app_id = $_GET['clear_app'];

    // Delete the application from the database
    $delete_sql = "DELETE FROM jobapplication WHERE application_id = ? AND candidate_id = ?";
    $delete_stmt = $conn->prepare($delete_sql);
    $delete_stmt->bind_param("ii", $app_id, $user_id);
    $delete_stmt->execute();
    $delete_stmt->close();

    // Redirect to avoid form resubmission on page reload
    header("Location: application.php");
    exit();
}

$sql = "
    SELECT 
        ja.application_id,
        u.name AS candidate_name,
        ep.company AS company_name,
        jl.job_title AS job_title,
        ja.applied_date,
        ja.status
    FROM jobapplication ja
    JOIN jobseeker_profile jp ON ja.candidate_id = jp.candidate_id
    JOIN users u ON jp.user_id = u.user_id
    JOIN job_listings jl ON ja.job_id = jl.job_id
    JOIN employerprofile ep ON jl.employer_id = ep.employer_id
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
    <title>Your Job Applications</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            margin: 0;
            padding: 0;
            background: linear-gradient(135deg, #89f7fe, #66a6ff);
            min-height: 100vh;
            color: #333;
        }

        .top-bar {
            background-color: #4b6cb7;
            padding: 15px 30px;
            color: white;
            display: flex;
            justify-content: space-between;
            align-items: center;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .logout-button {
            background-color: white;
            color: #4b6cb7;
            border: none;
            padding: 8px 15px;
            font-weight: bold;
            border-radius: 5px;
            cursor: pointer;
        }

        .logout-button:hover {
            background-color: #f0f0f0;
        }

        .container {
            max-width: 1000px;
            margin: 40px auto;
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 16px rgba(0,0,0,0.15);
        }

        h2 {
            text-align: center;
            color: #333;
            margin-bottom: 25px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 10px;
        }

        th, td {
            padding: 14px 12px;
            border-bottom: 1px solid #ddd;
            text-align: left;
        }

        th {
            background-color: #4b6cb7;
            color: white;
        }

        tr:hover {
            background-color: #f1f1f1;
        }

        .clear-btn {
            background-color: #f44336;
            color: white;
            padding: 6px 12px;
            border-radius: 5px;
            text-decoration: none;
        }

        .clear-btn:hover {
            background-color: #d32f2f;
        }

        p {
            text-align: center;
            font-size: 18px;
        }
    </style>
</head>
<body>

<div class="top-bar">
    <div><strong>Job Portal System</strong></div>
    <form method="POST" action="logout.php" style="margin: 0;">
        <button class="logout-button" type="submit">Logout</button>
    </form>
</div>

<div class="container">
    <h2>Your Job Applications</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <tr>
                <th>Candidate Name</th>
                <th>Company</th>
                <th>Job Title</th>
                <th>Applied Date</th>
                <th>Status</th>
                <th>Action</th>
            </tr>
            <?php while ($row = $result->fetch_assoc()): ?>
                <tr>
                    <td><?php echo htmlspecialchars($row['candidate_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['company_name']); ?></td>
                    <td><?php echo htmlspecialchars($row['job_title']); ?></td>
                    <td><?php echo htmlspecialchars($row['applied_date']); ?></td>
                    <td><?php echo htmlspecialchars($row['status']); ?></td>
                    <td>
                        <a href="?clear_app=<?php echo htmlspecialchars($row['application_id']); ?>" class="clear-btn" onclick="return confirm('Are you sure you want to clear this application?')">Clear</a>
                    </td>
                </tr>
            <?php endwhile; ?>
        </table>
    <?php else: ?>
        <p>You haven't applied for any jobs yet.</p>
    <?php endif; ?>
</div>

</body>
</html>

<?php
$stmt->close();
$conn->close();
?>

