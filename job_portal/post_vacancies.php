<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== "Employer") {
    exit("Error: Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Get employer_id using user_id
$query = "SELECT e.employer_id, u.name, e.company 
          FROM employerprofile e 
          JOIN users u ON u.user_id = e.user_id 
          WHERE e.user_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $user_id);
$stmt->execute();
$stmt->bind_result($employer_id, $employer_name, $company_name);
$stmt->fetch();
$stmt->close();

if (!$employer_id) {
    die("Error: Employer profile not found.");
}

// Handle Job Posting
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $job_title = trim($_POST['job_title']);
    $description = trim($_POST['description']);
    $location = trim($_POST['location']);
    $salary = trim($_POST['salary']);
    $category = trim($_POST['category']);

    $query = "INSERT INTO job_listings (employer_id, job_title, description, location, salary, category) 
              VALUES (?, ?, ?, ?, ?, ?)";
    $stmt = $conn->prepare($query);

    if (!$stmt) {
        die("Error: Job insertion query failed - " . $conn->error);
    }

    $stmt->bind_param("isssss", $employer_id, $job_title, $description, $location, $salary, $category);
    $stmt->execute();

    if ($stmt->affected_rows > 0) {
        $success_message = "Job posted successfully!";
    } else {
        $error_message = "Error: Failed to post job.";
    }

    $stmt->close();
}

// Fetch Posted Jobs
$query = "SELECT job_title, description, location, salary, category FROM job_listings WHERE employer_id = ?";
$stmt = $conn->prepare($query);
$stmt->bind_param("i", $employer_id);
$stmt->execute();
$result = $stmt->get_result();
?>


<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Post Vacancies</title>
    <style>
        :root {
            --primary-blue: #1E90FF;
            --light-blue: #E6F7FF;
            --dark-blue: #0066CC;
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
            width: 80%;
            max-width: 1000px;
            margin: 30px auto;
            padding: 30px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.1);
            border: 2px solid var(--primary-blue);
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

        h1, h2, h3 {
            color: var(--dark-blue);
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        h1 {
            font-size: 2.5rem;
            margin-bottom: 10px;
            border-bottom: 2px solid var(--primary-blue);
            padding-bottom: 10px;
        }

        h2 {
            font-size: 1.8rem;
            margin-top: 0;
            color: var(--primary-blue);
        }

        h3 {
            font-size: 1.5rem;
            margin: 25px 0 15px;
            padding-bottom: 8px;
            border-bottom: 1px dashed var(--primary-blue);
        }

        .form-group {
            margin: 20px 0;
            position: relative;
        }

        label {
            font-weight: 600;
            color: var(--dark-blue);
            display: block;
            margin-bottom: 8px;
            transition: all 0.3s ease;
        }

        input, textarea, select {
            width: 100%;
            padding: 12px;
            margin-top: 5px;
            border: 2px solid #ddd;
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: rgba(255,255,255,0.9);
        }

        input:focus, textarea:focus, select:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 3px var(--highlight);
            transform: scale(1.01);
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        button, .back-btn {
            padding: 15px 30px;
            color: white;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            position: relative;
            overflow: hidden;
        }

        button {
            background: linear-gradient(135deg, var(--primary-blue), var(--dark-blue));
        }

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        button:active {
            transform: translateY(1px);
        }

        .back-btn {
            display: inline-block;
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
            text-decoration: none;
            margin-right: 15px;
        }

        .back-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .job-listing {
            border: 2px solid #ddd;
            padding: 20px;
            margin: 20px 0;
            background-color: white;
            border-radius: 10px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.05);
        }

        .job-listing:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 15px rgba(0,0,0,0.1);
            border-color: var(--primary-blue);
        }

        .job-listing strong {
            color: var(--dark-blue);
            font-size: 1.2rem;
        }

        .job-listing em {
            color: #555;
            font-style: normal;
            display: block;
            margin-top: 8px;
        }

        .success-message {
            color: var(--success);
            background-color: rgba(76, 175, 80, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid var(--success);
            margin: 20px 0;
            animation: slideIn 0.5s ease;
        }

        .error-message {
            color: var(--error);
            background-color: rgba(244, 67, 54, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid var(--error);
            margin: 20px 0;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-20px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
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
    <h1><?php echo htmlspecialchars($company_name); ?></h1>
    <h2>Welcome, <?php echo htmlspecialchars($employer_name); ?>!</h2>

    <?php if(isset($success_message)): ?>
        <div class="success-message"><?php echo $success_message; ?></div>
    <?php endif; ?>
    
    <?php if(isset($error_message)): ?>
        <div class="error-message"><?php echo $error_message; ?></div>
    <?php endif; ?>

    <!-- Back to Dashboard Button -->
    <a href="employer_dashboard.php" class="back-btn">Back to Dashboard</a>

    <h3>Post a New Job</h3>
    <form method="POST" id="jobForm">
        <div class="form-group">
            <label for="job_title">Job Title:</label>
            <input type="text" name="job_title" id="job_title" required>
        </div>
        <div class="form-group">
            <label for="description">Description:</label>
            <textarea name="description" id="description" required></textarea>
        </div>
        <div class="form-group">
            <label for="location">Location:</label>
            <input type="text" name="location" id="location" required>
        </div>
        <div class="form-group">
            <label for="salary">Salary:</label>
            <input type="text" name="salary" id="salary" required>
        </div>
        <div class="form-group">
            <label for="category">Category:</label>
            <input type="text" name="category" id="category" required>
        </div>
        <button type="submit" id="submitBtn">Post Job</button>
    </form>

    <h3>Your Job Listings</h3>
    <?php while ($job = $result->fetch_assoc()) { ?>
        <div class="job-listing">
            <strong><?php echo htmlspecialchars($job['job_title']); ?></strong>
            <p><?php echo nl2br(htmlspecialchars($job['description'])); ?></p>
            <em>Location: <?php echo htmlspecialchars($job['location']); ?></em>
            <em>Salary: <?php echo htmlspecialchars($job['salary']); ?></em>
            <em>Category: <?php echo htmlspecialchars($job['category']); ?></em>
        </div>
    <?php } ?>
</div>

<script>
    // Highlight elements when mouse is near
    document.addEventListener('mousemove', function(e) {
        const elements = document.querySelectorAll('.form-group, .job-listing, button, .back-btn');
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
        if (e.target.tagName === 'INPUT' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'LABEL') {
            const label = e.target.tagName === 'LABEL' ? e.target : document.querySelector(`label[for="${e.target.id}"]`);
            if (label) {
                label.classList.add('click-effect');
                setTimeout(() => {
                    label.classList.remove('click-effect');
                }, 500);
            }
        }
        
        if (e.target.classList.contains('job-listing') || e.target.classList.contains('back-btn') || e.target.id === 'submitBtn') {
            e.target.classList.add('click-effect');
            setTimeout(() => {
                e.target.classList.remove('click-effect');
            }, 500);
        }
    });

    // Form submission animation
    document.getElementById('jobForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = 'Posting...';
        btn.style.opacity = '0.8';
    });
</script>

<?php
$stmt->close();
$conn->close();
?>
</body>
</html>
