<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower($_SESSION['role']) !== 'employer') {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Get employer details
$stmt = $conn->prepare("SELECT employer_id, name, company FROM employerprofile WHERE user_id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 0) {
    exit("Error: Employer profile not found.");
}

$employer = $result->fetch_assoc();
$employer_id = $employer['employer_id'];
$employer_name = $employer['name'];
$company_name = $employer['company'];

// Get jobseeker applications
$sql = "
    SELECT
        jp.candidate_id,
        u.name AS candidate_name,
        jl.job_title,
        jp.resume,
        jp.skills,
        jp.experience,
        jp.applied_date,
        ja.status,
        ja.application_id
    FROM job_listings jl
    JOIN jobseeker_profile jp ON jp.job_id = jl.job_id
    JOIN users u ON jp.user_id = u.user_id
    JOIN jobapplication ja ON jp.candidate_id = ja.candidate_id AND jp.job_id = ja.job_id
    WHERE jl.employer_id = ?
    ORDER BY jp.applied_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Profiles</title>
    <style>
        :root {
            --primary-blue: #1E90FF;
            --dark-blue: #0066CC;
            --light-blue: #E6F7FF;
            --highlight: rgba(30, 144, 255, 0.2);
            --success: #4CAF50;
            --error: #F44336;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #E6F7FF, #B3E0FF);
            margin: 0;
            padding: 20px;
            min-height: 100vh;
            animation: gradientPulse 15s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradientPulse {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .container {
            width: 90%;
            max-width: 1200px;
            margin: 30px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border: 3px solid var(--primary-blue);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--primary-blue), var(--dark-blue));
            animation: borderFlow 3s linear infinite;
        }

        @keyframes borderFlow {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.15);
        }

        .topbar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 15px;
            margin-bottom: 20px;
        }

        .topbar h2 {
            color: var(--dark-blue);
            font-size: 28px;
            margin: 0;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .logout-button, .back-button {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            padding: 12px 25px;
            border: none;
            border-radius: 30px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            text-decoration: none;
            display: inline-block;
        }

        .logout-button:hover, .back-button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: top;
            transition: all 0.3s ease;
        }

        th {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
        }

        tr:nth-child(even) {
            background-color: rgba(230, 247, 255, 0.5);
        }

        tr:hover {
            background-color: var(--highlight);
            transform: scale(1.005);
        }

        form select {
            padding: 8px;
            border: 2px solid #ddd;
            border-radius: 6px;
            background-color: white;
            transition: all 0.3s ease;
        }

        form select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px var(--highlight);
        }

        form button {
            padding: 8px 16px;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        form button:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #555;
        }

        /* Highlight effect */
        .highlight {
            background-color: var(--highlight);
            transition: background-color 0.3s ease;
        }

        /* Click animation */
        .click-effect {
            animation: clickFlash 0.5s ease;
        }

        @keyframes clickFlash {
            0% { transform: scale(0.98); box-shadow: 0 0 0 5px var(--highlight); }
            100% { transform: scale(1); box-shadow: none; }
        }
    </style>
</head>
<body>

<div class="container">
    <div class="topbar">
        <h2>Application Profiles - <?php echo htmlspecialchars($company_name); ?></h2>
        <form action="index.php" method="post">
            <button class="logout-button" type="submit">Logout</button>
        </form>
    </div>

    <!-- Back Button -->
    <div style="margin-bottom: 20px;">
        <a href="employer_dashboard.php" class="back-button">Back to Dashboard</a>
    </div>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Candidate Name</th>
                    <th>Job Title</th>
                    <th>Resume</th>
                    <th>Skills</th>
                    <th>Experience</th>
                    <th>Applied Date</th>
                    <th>Status</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['candidate_name']) ?></td>
                        <td><?= htmlspecialchars($row['job_title']) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['resume'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['skills'])) ?></td>
                        <td><?= nl2br(htmlspecialchars($row['experience'])) ?></td>
                        <td><?= htmlspecialchars($row['applied_date']) ?></td>
                        <td>
                            <form method="POST" action="update_status.php">
                                <input type="hidden" name="application_id" value="<?= $row['application_id'] ?>">
                                <select name="status" required>
                                    <option value="Pending" <?= $row['status'] === 'Pending' ? 'selected' : '' ?>>Pending</option>
                                    <option value="Accepted" <?= $row['status'] === 'Accepted' ? 'selected' : '' ?>>Accepted</option>
                                    <option value="Rejected" <?= $row['status'] === 'Rejected' ? 'selected' : '' ?>>Rejected</option>
                                </select>
                                <button type="submit">Update</button>
                            </form>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p style="text-align: center; font-size: 18px; color: var(--dark-blue);">No applications found for your posted jobs.</p>
    <?php endif; ?>

    <div class="footer">
        <p>&copy; 2025 <?php echo htmlspecialchars($company_name); ?>. All rights reserved.</p>
    </div>
</div>

<script>
    // Highlight elements when mouse is near
    document.addEventListener('mousemove', function(e) {
        const elements = document.querySelectorAll('tr, .logout-button, .back-button, select, button');
        const mouseX = e.clientX;
        const mouseY = e.clientY;
        
        elements.forEach(el => {
            const rect = el.getBoundingClientRect();
            const elX = rect.left + rect.width / 2;
            const elY = rect.top + rect.height / 2;
            
            const distance = Math.sqrt(Math.pow(mouseX - elX, 2) + Math.pow(mouseY - elY, 2));
            
            if (distance < 150) { // Highlight radius
                el.classList.add('highlight');
            } else {
                el.classList.remove('highlight');
            }
        });
    });

    // Click animation
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'TR' || e.target.tagName === 'BUTTON' || e.target.tagName === 'SELECT') {
            e.target.classList.add('click-effect');
            setTimeout(() => {
                e.target.classList.remove('click-effect');
            }, 500);
        }
    });
</script>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>