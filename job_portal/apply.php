<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== "job seeker") {
    exit("Error: Unauthorized access.");
}

$user_id = $_SESSION['user_id'];
$job_id = isset($_GET['job_id']) ? intval($_GET['job_id']) : 0;

// Validate job ID
if ($job_id <= 0) {
    exit("Error: Job not specified.");
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $resume = trim($_POST['resume']);
    $skills = trim($_POST['skills']);
    $experience = trim($_POST['experience']);
    $applied_date = date('Y-m-d');

    if (empty($resume) || empty($skills) || empty($experience)) {
        echo "<p style='color: red;'>All fields are required.</p>";
    } else {
        // ✅ Check if already applied
        $check_sql = "
            SELECT jp.candidate_id 
            FROM jobseeker_profile jp
            JOIN jobapplication ja ON jp.candidate_id = ja.candidate_id
            WHERE jp.user_id = ? AND jp.job_id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("ii", $user_id, $job_id);
        $check_stmt->execute();
        $check_stmt->store_result();

        if ($check_stmt->num_rows > 0) {
            echo "<p style='color: orange;'>You have already applied for this job.</p>";
            $check_stmt->close();
        } else {
            $check_stmt->close();

            // ✅ Get user's name
            $user_result = $conn->query("SELECT name FROM users WHERE user_id = $user_id");
            if ($user_result && $user_result->num_rows > 0) {
                $user_data = $user_result->fetch_assoc();
                $name = $user_data['name'];
            } else {
                exit("Error: User not found.");
            }

            // ✅ Insert into jobseeker_profile
            $stmt = $conn->prepare("INSERT INTO jobseeker_profile (user_id, job_id, resume, skills, experience, applied_date) VALUES (?, ?, ?, ?, ?, ?)");
            if ($stmt) {
                $stmt->bind_param("iissss", $user_id, $job_id, $resume, $skills, $experience, $applied_date);
                $stmt->execute();

                if ($stmt->affected_rows > 0) {
                    $candidate_id = $conn->insert_id;

                    // ✅ Insert into jobapplication
                    $status = "Pending";
                    $app_stmt = $conn->prepare("INSERT INTO jobapplication (job_id, candidate_id, status, applied_date) VALUES (?, ?, ?, ?)");
                    $app_stmt->bind_param("iiss", $job_id, $candidate_id, $status, $applied_date);
                    $app_stmt->execute();
                    $app_stmt->close();

                    header("Location: application.php?applied=1");
                    exit();
                } else {
                    echo "<p style='color: red;'>Error: Failed to submit application.</p>";
                }
                $stmt->close();
            } else {
                echo "<p style='color: red;'>Error: Could not prepare statement.</p>";
            }
        }
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Job</title>
    <style>
        :root {
            --blue-primary: #1976d2;
            --blue-dark: #0d47a1;
            --blue-light: #64b5f6;
            --blue-accent: #1e88e5;
            --highlight: rgba(30, 136, 229, 0.2);
        }

        body {
            font-family: 'Arial', sans-serif;
            padding: 20px;
            background: linear-gradient(135deg, var(--blue-primary), var(--blue-accent));
            color: #fff;
            margin: 0;
            min-height: 100vh;
            animation: gradientPulse 15s ease infinite;
            background-size: 200% 200%;
        }

        @keyframes gradientPulse {
            0% { background-position: 0% 50%; }
            50% { background-position: 100% 50%; }
            100% { background-position: 0% 50%; }
        }

        h2 {
            text-align: center;
            font-size: 2.5em;
            margin-bottom: 20px;
            color: #fff;
            text-shadow: 0 2px 4px rgba(0,0,0,0.3);
            animation: textGlow 2s ease-in-out infinite alternate;
        }

        @keyframes textGlow {
            from { text-shadow: 0 0 5px #fff, 0 0 10px #fff, 0 0 15px var(--blue-light); }
            to { text-shadow: 0 0 10px #fff, 0 0 20px var(--blue-light), 0 0 30px var(--blue-accent); }
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 8px 20px rgba(0, 0, 0, 0.2);
            max-width: 600px;
            margin: auto;
            border: 3px solid var(--blue-light);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .form-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 5px;
            background: linear-gradient(90deg, var(--blue-primary), var(--blue-accent));
            animation: borderFlow 3s linear infinite;
        }

        @keyframes borderFlow {
            0% { transform: translateX(-100%); }
            100% { transform: translateX(100%); }
        }

        .form-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 25px rgba(0, 0, 0, 0.3);
        }

        label {
            display: block;
            margin-top: 15px;
            font-weight: bold;
            color: var(--blue-dark);
            font-size: 1.1em;
            transition: all 0.3s ease;
        }

        label:hover {
            color: var(--blue-accent);
            transform: translateX(5px);
        }

        textarea, input[type="text"] {
            width: 100%;
            padding: 12px;
            margin-top: 8px;
            border: 2px solid var(--blue-light);
            border-radius: 8px;
            transition: all 0.3s ease;
            font-size: 1em;
            background-color: rgba(255,255,255,0.9);
        }

        textarea {
            height: 120px;
            resize: vertical;
        }

        textarea:focus, input[type="text"]:focus {
            outline: none;
            border-color: var(--blue-accent);
            box-shadow: 0 0 0 3px var(--highlight);
            transform: scale(1.01);
        }

        button {
            background: linear-gradient(to right, var(--blue-primary), var(--blue-accent));
            color: white;
            border: none;
            padding: 15px 30px;
            font-size: 1.1em;
            margin-top: 25px;
            cursor: pointer;
            border-radius: 8px;
            transition: all 0.3s ease;
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
            display: block;
            width: 100%;
            font-weight: bold;
            letter-spacing: 1px;
            position: relative;
            overflow: hidden;
        }

        button::after {
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

        button:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 12px rgba(0,0,0,0.3);
        }

        button:hover::after {
            left: 100%;
        }

        button:active {
            transform: translateY(1px);
        }

        .back-button {
            display: inline-block;
            margin-top: 20px;
            padding: 12px 25px;
            background-color: var(--blue-dark);
            color: white;
            text-decoration: none;
            border-radius: 8px;
            transition: all 0.3s ease;
            text-align: center;
            border: 2px solid transparent;
        }

        .back-button:hover {
            background-color: transparent;
            color: var(--blue-dark);
            border-color: var(--blue-dark);
            transform: translateX(-5px);
        }

        p {
            text-align: center;
            font-size: 1.2em;
            padding: 10px;
            border-radius: 5px;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-20px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .click-highlight {
            animation: clickFlash 0.5s ease;
        }

        @keyframes clickFlash {
            0% { background-color: var(--highlight); }
            100% { background-color: transparent; }
        }

        .success-message {
            color: #2e7d32;
            background-color: rgba(46, 125, 50, 0.1);
            padding: 15px;
            border-radius: 8px;
            border-left: 5px solid #2e7d32;
            animation: slideIn 0.5s ease;
        }

        @keyframes slideIn {
            from { transform: translateY(-30px); opacity: 0; }
            to { transform: translateY(0); opacity: 1; }
        }
    </style>
</head>
<body>

<div class="form-container">
    <h2>Apply for Job</h2>

    <?php
    if (isset($_GET['applied'])) {
        echo "<p class='success-message'>Application submitted successfully!</p>";
    }
    ?>

    <form method="POST" id="applicationForm">
        <input type="hidden" name="job_id" value="<?php echo htmlspecialchars($job_id); ?>">

        <label for="resume">Resume:</label>
        <textarea name="resume" id="resume" required></textarea>

        <label for="skills">Skills:</label>
        <textarea name="skills" id="skills" required></textarea>

        <label for="experience">Experience:</label>
        <textarea name="experience" id="experience" required></textarea>

        <button type="submit" id="submitBtn">Submit Application</button>
    </form>

    <a href="jobseeker_dashboard.php" class="back-button">Back to Dashboard</a>
</div>

<script>
    document.addEventListener('click', function(e) {
        if (e.target.tagName === 'LABEL' || e.target.tagName === 'TEXTAREA' || e.target.tagName === 'INPUT') {
            const label = e.target.tagName === 'LABEL' ? e.target : document.querySelector(`label[for="${e.target.id}"]`);
            if (label) {
                label.classList.add('click-highlight');
                setTimeout(() => {
                    label.classList.remove('click-highlight');
                }, 500);
            }
        }

        if (e.target === document.getElementById('submitBtn')) {
            e.target.classList.add('click-highlight');
            setTimeout(() => {
                e.target.classList.remove('click-highlight');
            }, 500);
        }
    });

    document.getElementById('applicationForm').addEventListener('submit', function(e) {
        const btn = document.getElementById('submitBtn');
        btn.innerHTML = 'Submitting...';
        btn.style.background = 'linear-gradient(to right, #0d47a1, #1e88e5)';
    });
</script>

</body>
</html>

<?php $conn->close(); ?>
