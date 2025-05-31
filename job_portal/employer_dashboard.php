<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'employer') {
    exit("Unauthorized access.");
}

$user_id = $_SESSION['user_id'];

// Fetch employer profile from employerprofile table
$sql_profile = "SELECT * FROM employerprofile WHERE user_id = ?";
$stmt_profile = $conn->prepare($sql_profile);
$stmt_profile->bind_param("i", $user_id);
$stmt_profile->execute();
$result_profile = $stmt_profile->get_result();

if ($result_profile->num_rows === 1) {
    $profile = $result_profile->fetch_assoc();
    $company = $profile['company'];
    $contact_info = $profile['contact_info'];
    $employer_name = $profile['name'];
} else {
    $company = "No company profile loaded.";
    $contact_info = "No contact information available.";
    $employer_name = "Employer";
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Employer Dashboard</title>
    <style>
        :root {
            --sky-blue: #87CEEB;
            --deep-sky-blue: #00BFFF;
            --light-blue: #E6F7FF;
            --dark-blue: #1E90FF;
            --highlight: rgba(30, 144, 255, 0.2);
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, var(--sky-blue), var(--deep-sky-blue));
            margin: 0;
            padding: 0;
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            animation: gradientPulse 15s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradientPulse {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        .logout-container {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 20px;
            z-index: 10;
        }

        .logout-button {
            padding: 12px 25px;
            background-color: #FF6B6B;
            color: white;
            text-decoration: none;
            border-radius: 30px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
            border: 2px solid transparent;
        }

        .logout-button:hover {
            background-color: white;
            color: #FF6B6B;
            border-color: #FF6B6B;
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.15);
        }

        .container {
            width: 90%;
            max-width: 900px;
            margin: 50px auto;
            padding: 40px;
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.15);
            position: relative;
            overflow: hidden;
            border: 3px solid var(--dark-blue);
            transition: all 0.3s ease;
        }

        .container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--sky-blue), var(--dark-blue));
            animation: borderFlow 3s linear infinite;
        }

        @keyframes borderFlow {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.2);
        }

        .company-info {
            text-align: center;
            color: var(--dark-blue);
            font-size: 24px;
            margin-bottom: 20px;
            padding: 15px;
            border-radius: 10px;
            background-color: var(--light-blue);
            transition: all 0.3s ease;
        }

        .company-info:hover {
            transform: scale(1.02);
            box-shadow: 0 4px 8px rgba(0,0,0,0.1);
        }

        .company-info strong {
            color: var(--dark-blue);
            font-weight: 700;
        }

        .welcome-message {
            text-align: center;
            font-size: 24px;
            color: #333;
            margin-bottom: 40px;
            font-weight: 600;
            text-shadow: 0 2px 4px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
        }

        .welcome-message:hover {
            color: var(--dark-blue);
            transform: scale(1.05);
        }

        .dashboard-buttons {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 25px;
        }

        .dashboard-buttons a {
            padding: 20px 30px;
            color: white;
            text-decoration: none;
            border-radius: 12px;
            font-size: 18px;
            font-weight: 600;
            box-shadow: 0 4px 8px rgba(0, 0, 0, 0.15);
            transition: all 0.3s ease;
            width: 280px;
            text-align: center;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
            letter-spacing: 0.5px;
        }

        .dashboard-buttons a::after {
            content: '';
            position: absolute;
            top: -50%;
            left: -50%;
            width: 200%;
            height: 200%;
            background: rgba(255,255,255,0.1);
            transform: rotate(30deg);
            transition: all 0.3s ease;
        }

        .dashboard-buttons a:hover {
            box-shadow: 0 8px 15px rgba(0, 0, 0, 0.2);
            transform: translateY(-5px);
            border-color: white;
        }

        .dashboard-buttons a:hover::after {
            left: 100%;
        }

        .dashboard-buttons a:nth-child(1) {
            background: linear-gradient(135deg, var(--deep-sky-blue), var(--dark-blue));
        }

        .dashboard-buttons a:nth-child(1):hover {
            background: linear-gradient(135deg, var(--dark-blue), var(--deep-sky-blue));
        }

        .dashboard-buttons a:nth-child(2) {
            background: linear-gradient(135deg, #4CAF50, #2E7D32);
        }

        .dashboard-buttons a:nth-child(2):hover {
            background: linear-gradient(135deg, #2E7D32, #4CAF50);
        }

        .dashboard-buttons a:nth-child(3) {
            background: linear-gradient(135deg, #FF9800, #F57C00);
            color: white;
        }

        .dashboard-buttons a:nth-child(3):hover {
            background: linear-gradient(135deg, #F57C00, #FF9800);
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

<div class="logout-container">
    <a href="index.php" class="logout-button">Logout</a>
</div>

<div class="container">
    <!-- Display company and contact info -->
    <div class="company-info">
        <strong>Company:</strong> <?php echo htmlspecialchars($company); ?><br>
        <strong>Contact Info:</strong> <?php echo htmlspecialchars($contact_info); ?>
    </div>

    <div class="welcome-message">
        Welcome, <?php echo htmlspecialchars($employer_name); ?>!
    </div>

    <div class="dashboard-buttons">
        <a href="post_vacancies.php">Post New Vacancies</a>
        <a href="application_profiles.php">View Application Profiles</a>
        <a href="application_status.php">Manage Application Status</a>
    </div>
</div>

<script>
    // Highlight elements when mouse is near
    document.addEventListener('mousemove', function(e) {
        const elements = document.querySelectorAll('.dashboard-buttons a, .company-info, .welcome-message');
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
        if (e.target.classList.contains('dashboard-buttons') || 
            e.target.classList.contains('company-info') || 
            e.target.classList.contains('welcome-message') ||
            e.target.tagName === 'A') {
            
            const element = e.target;
            element.classList.add('click-effect');
            setTimeout(() => {
                element.classList.remove('click-effect');
            }, 500);
        }
    });
</script>

<?php
$stmt_profile->close();
$conn->close();
?>

</body>
</html>