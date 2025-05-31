<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'employer') {
    exit("Error: Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Fetch employer_id
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

// Fetch applications
$sql = "
    SELECT 
        u.name AS candidate_name,
        jl.job_title,
        ja.applied_date,
        ja.updated_at,
        ja.status,
        ja.application_id
    FROM job_listings jl
    JOIN jobseeker_profile jp ON jl.job_id = jp.job_id
    JOIN users u ON jp.user_id = u.user_id
    JOIN jobapplication ja ON jp.candidate_id = ja.candidate_id AND jp.job_id = ja.job_id
    WHERE jl.employer_id = ?
    ORDER BY ja.applied_date DESC
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Application Status - <?php echo htmlspecialchars($company_name); ?></title>
    <style>
        :root {
            --primary-blue: #1E90FF;
            --dark-blue: #0066CC;
            --light-blue: #E6F7FF;
            --highlight: rgba(30, 144, 255, 0.3);
            --success: #4CAF50;
            --error: #F44336;
            --danger: #dc3545;
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

        .top-bar {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            padding: 20px;
            border-radius: 10px;
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 30px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        .top-bar::after {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 100%;
            background: linear-gradient(90deg, 
                      rgba(255,255,255,0.1) 0%, 
                      rgba(255,255,255,0.3) 50%, 
                      rgba(255,255,255,0.1) 100%);
            animation: shine 2s infinite;
        }

        @keyframes shine {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .top-bar strong {
            font-size: 1.2em;
            text-shadow: 0 2px 4px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }

        .logout-button, .delete-button {
            background-color: white;
            color: var(--primary-blue);
            border: none;
            padding: 12px 25px;
            font-weight: 600;
            border-radius: 30px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
            z-index: 1;
        }

        .logout-button:hover {
            background-color: var(--light-blue);
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .delete-button {
            background-color: var(--danger);
            color: white;
        }

        .delete-button:hover {
            background-color: #c82333;
            transform: translateY(-3px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        h2 {
            margin: 25px 0;
            color: var(--dark-blue);
            text-align: center;
            font-size: 28px;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            position: relative;
        }

        h2::after {
            content: '';
            display: block;
            width: 100px;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--dark-blue));
            margin: 10px auto;
            border-radius: 2px;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 25px 0;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
            background-color: white;
            border-radius: 10px;
            overflow: hidden;
        }

        th, td {
            padding: 15px;
            border: 1px solid #ddd;
            text-align: left;
            vertical-align: middle;
            transition: all 0.3s ease;
        }

        th {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
            color: white;
            font-weight: 600;
            position: sticky;
            top: 0;
            text-shadow: 0 1px 2px rgba(0,0,0,0.2);
        }

        tr:nth-child(even) {
            background-color: rgba(230, 247, 255, 0.5);
        }

        tr:hover {
            background-color: var(--highlight);
            transform: scale(1.005);
        }

        .footer {
            text-align: center;
            margin-top: 40px;
            padding-top: 20px;
            border-top: 1px solid #ddd;
            font-size: 14px;
            color: #555;
        }

        .empty-message {
            text-align: center;
            color: var(--dark-blue);
            font-size: 18px;
            padding: 30px;
            background-color: rgba(230, 247, 255, 0.7);
            border-radius: 10px;
            margin: 20px 0;
        }

        /* Status badges */
        .status-pending {
            color: #FF9800;
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .status-accepted {
            color: var(--success);
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }
        .status-rejected {
            color: var(--error);
            font-weight: 600;
            text-shadow: 0 1px 2px rgba(0,0,0,0.1);
        }

        /* Highlight effect */
        .highlight {
            background-color: var(--highlight);
            transition: background-color 0.3s ease;
            box-shadow: 0 0 10px rgba(30, 144, 255, 0.5);
        }

        /* Click animation */
        .click-effect {
            animation: clickFlash 0.5s ease;
        }

        @keyframes clickFlash {
            0% { transform: scale(0.98); box-shadow: 0 0 0 8px var(--highlight); }
            100% { transform: scale(1); box-shadow: none; }
        }

        .confirmation-dialog {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-color: rgba(0,0,0,0.5);
            z-index: 1000;
            justify-content: center;
            align-items: center;
        }

        .dialog-content {
            background-color: white;
            padding: 30px;
            border-radius: 10px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.3);
            text-align: center;
            max-width: 400px;
            width: 90%;
        }

        .dialog-buttons {
            margin-top: 20px;
            display: flex;
            justify-content: center;
            gap: 15px;
        }

        .dialog-button {
            padding: 10px 20px;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s;
        }

        .confirm-button {
            background-color: var(--danger);
            color: white;
        }

        .confirm-button:hover {
            background-color: #c82333;
        }

        .cancel-button {
            background-color: #6c757d;
            color: white;
        }

        .cancel-button:hover {
            background-color: #5a6268;
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <div>
            <strong><?php echo htmlspecialchars($company_name); ?></strong><br>
            <span>Welcome, <?php echo htmlspecialchars($employer_name); ?></span>
        </div>
        <form method="POST" action="logout.php">
            <button class="logout-button" type="submit">Logout</button>
        </form>
    </div>

    <h2>Application Status Dashboard</h2>

    <?php if ($result->num_rows > 0): ?>
        <table>
            <thead>
                <tr>
                    <th>Candidate Name</th>
                    <th>Applied Job</th>
                    <th>Applied Date</th>
                    <th>Updated Date</th>
                    <th>Status</th>
                    <th>Action</th>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr id="row-<?= $row['application_id'] ?>">
                        <td class="highlight-text"><?= htmlspecialchars($row['candidate_name']) ?></td>
                        <td class="highlight-text"><?= htmlspecialchars($row['job_title']) ?></td>
                        <td><?= htmlspecialchars($row['applied_date']) ?></td>
                        <td><?= htmlspecialchars($row['updated_at']) ?></td>
                        <td id="status-<?= $row['application_id'] ?>" 
                            class="status-<?= strtolower($row['status']) ?> highlight-text">
                            <?= htmlspecialchars($row['status']) ?>
                        </td>
                        <td>
                            <button class="delete-button" 
                                    onclick="confirmDelete(<?= $row['application_id'] ?>)">
                                Delete
                            </button>
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <div class="empty-message">
            No applications found for your posted jobs.
        </div>
    <?php endif; ?>

    <div class="footer">
        <p>&copy; 2025 <?php echo htmlspecialchars($company_name); ?>. All rights reserved.</p>
    </div>
</div>

<!-- Confirmation Dialog -->
<div class="confirmation-dialog" id="confirmationDialog">
    <div class="dialog-content">
        <h3>Confirm Deletion</h3>
        <p>Are you sure you want to delete this application? This action cannot be undone.</p>
        <div class="dialog-buttons">
            <button class="dialog-button confirm-button" id="confirmDelete">Delete</button>
            <button class="dialog-button cancel-button" id="cancelDelete">Cancel</button>
        </div>
    </div>
</div>

<script>
    let currentApplicationId = null;

    // Highlight elements when mouse is near
    document.addEventListener('mousemove', function(e) {
        const elements = document.querySelectorAll('tr, .logout-button, .delete-button, .highlight-text');
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
        if (e.target.tagName === 'TR' || e.target.tagName === 'BUTTON' || e.target.classList.contains('highlight-text')) {
            e.target.classList.add('click-effect');
            setTimeout(() => {
                e.target.classList.remove('click-effect');
            }, 500);
        }
    });

    // Show confirmation dialog
    function confirmDelete(applicationId) {
        currentApplicationId = applicationId;
        document.getElementById('confirmationDialog').style.display = 'flex';
    }

    // Hide confirmation dialog
    document.getElementById('cancelDelete').addEventListener('click', function() {
        document.getElementById('confirmationDialog').style.display = 'none';
        currentApplicationId = null;
    });

    // Handle delete confirmation
    document.getElementById('confirmDelete').addEventListener('click', function() {
        if (currentApplicationId) {
            deleteApplication(currentApplicationId);
        }
        document.getElementById('confirmationDialog').style.display = 'none';
    });

    // JavaScript function to delete application
    function deleteApplication(applicationId) {
        // Create an AJAX request to delete the application
        var xhr = new XMLHttpRequest();
        xhr.open("POST", "delete_application.php", true);
        xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");
        xhr.onreadystatechange = function() {
            if (xhr.readyState == 4 && xhr.status == 200) {
                // Remove the row from the table
                const row = document.getElementById('row-' + applicationId);
                if (row) {
                    row.style.transition = 'all 0.3s ease';
                    row.style.opacity = '0';
                    row.style.transform = 'translateX(-100%)';
                    setTimeout(() => {
                        row.remove();
                    }, 300);
                }
                
                // Show a success message
                alert('Application deleted successfully!');
            }
        };
        xhr.send("application_id=" + applicationId);
    }
</script>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
