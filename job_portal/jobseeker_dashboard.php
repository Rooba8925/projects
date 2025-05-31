<?php
session_start();

if (!isset($_SESSION['user_id']) || strtolower(trim($_SESSION['role'])) !== 'job seeker') {
    echo "Error: Unauthorized access.";
    exit;
}

$user_id = $_SESSION['user_id'];
$mysqli = new mysqli("localhost", "root", "", "job_portal");

if ($mysqli->connect_error) {
    die("Connection failed: " . $mysqli->connect_error);
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Seeker Dashboard</title>
    <style>
        :root {
            --primary-blue: #1a73e8;
            --dark-blue: #0d47a1;
            --light-blue: #e8f0fe;
            --accent-blue: #4285f4;
            --highlight: rgba(66, 133, 244, 0.2);
        }

        body {
            margin: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #e3f2fd, #bbdefb, #90caf9);
            min-height: 100vh;
            padding: 0 20px 40px 20px;
            color: #202124;
        }

        h2 {
            text-align: center;
            padding: 20px 0;
            background-color: var(--primary-blue);
            color: white;
            margin: 0 -20px 30px -20px;
            box-shadow: 0 4px 6px rgba(0, 0, 0, 0.1);
            font-size: 28px;
            position: relative;
        }

        .logout-container {
            position: absolute;
            top: 20px;
            right: 20px;
        }

        .button-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .btn {
            padding: 12px 24px;
            margin: 5px;
            background-color: var(--primary-blue);
            color: white;
            border: none;
            border-radius: 6px;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }

        .btn:hover {
            background-color: var(--dark-blue);
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .logout-btn {
            background-color: #d32f2f;
        }

        .logout-btn:hover {
            background-color: #b71c1c;
        }

        .job-container {
            background-color: rgba(255, 255, 255, 0.95);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 25px;
            box-shadow: 0 6px 10px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
            border: 2px solid transparent;
        }

        .job-container::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            height: 4px;
            background: linear-gradient(90deg, var(--primary-blue), var(--accent-blue));
            transform: scaleX(0);
            transform-origin: left;
            transition: transform 0.3s ease;
        }

        .job-container:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 20px rgba(0, 0, 0, 0.15);
            border-color: var(--accent-blue);
        }

        .job-container:hover::before {
            transform: scaleX(1);
        }

        .job-container:active {
            transform: translateY(-2px);
        }

        .job-info {
            margin-bottom: 10px;
            transition: all 0.3s ease;
        }

        .job-container:hover .job-info {
            transform: translateX(5px);
        }

        .job-container strong {
            color: var(--primary-blue);
        }

        .applied-btn {
            background-color: #5f6368;
            color: white;
            padding: 10px 20px;
            border-radius: 6px;
            border: none;
            cursor: not-allowed;
            font-weight: 500;
        }

        #filterForm {
            display: none;
            background-color: rgba(255,255,255,0.95);
            padding: 20px;
            border-radius: 12px;
            margin-bottom: 25px;
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
            border: 2px solid var(--accent-blue);
            animation: pulseBorder 2s infinite;
        }

        @keyframes pulseBorder {
            0% { border-color: var(--accent-blue); }
            50% { border-color: var(--primary-blue); }
            100% { border-color: var(--accent-blue); }
        }

        #filterForm select,
        #filterForm input[type="number"] {
            padding: 10px;
            margin: 8px;
            border-radius: 5px;
            border: 1px solid #ccc;
            transition: all 0.3s ease;
            background-color: var(--light-blue);
        }

        #filterForm select:focus,
        #filterForm input[type="number"]:focus {
            outline: none;
            border-color: var(--primary-blue);
            box-shadow: 0 0 0 2px var(--highlight);
        }

        label {
            font-weight: bold;
            color: var(--primary-blue);
            margin-right: 5px;
        }

        form input[type="submit"] {
            margin-top: 15px;
            padding: 10px 20px;
        }

        .highlight {
            background-color: var(--highlight);
            transition: background-color 0.3s ease;
        }

        /* Animation for job containers when they come into view */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .job-container {
            animation: fadeInUp 0.5s ease forwards;
            opacity: 0;
        }

        .job-container:nth-child(1) { animation-delay: 0.1s; }
        .job-container:nth-child(2) { animation-delay: 0.2s; }
        .job-container:nth-child(3) { animation-delay: 0.3s; }
        .job-container:nth-child(4) { animation-delay: 0.4s; }
        .job-container:nth-child(5) { animation-delay: 0.5s; }
        .job-container:nth-child(6) { animation-delay: 0.6s; }
    </style>
</head>
<body>

<h2>Welcome to Your Job Dashboard
    <div class="logout-container">
        <a href="index.php" class="btn logout-btn">Logout</a>
    </div>
</h2>

<div class="button-container">
    <a href="application.php" class="btn">View Applications</a>
    <button onclick="toggleFilter()" class="btn">Filter Jobs</button>
</div>

<div id="filterForm">
    <form method="get" action="jobseeker_dashboard.php">
        <label>Company:</label>
        <select name="company">
            <option value="">-- Any --</option>
            <?php
            $company_result = $mysqli->query("SELECT DISTINCT ep.company FROM employerprofile ep JOIN job_listings jl ON ep.employer_id = jl.employer_id");
            while ($row = $company_result->fetch_assoc()) {
                $selected = ($_GET['company'] ?? '') == $row['company'] ? 'selected' : '';
                echo "<option value='".htmlspecialchars($row['company'])."' $selected>".htmlspecialchars($row['company'])."</option>";
            }
            ?>
        </select>

        <label>Job Title:</label>
        <select name="title">
            <option value="">-- Any --</option>
            <?php
            $title_result = $mysqli->query("SELECT DISTINCT job_title FROM job_listings");
            while ($row = $title_result->fetch_assoc()) {
                $selected = ($_GET['title'] ?? '') == $row['job_title'] ? 'selected' : '';
                echo "<option value='".htmlspecialchars($row['job_title'])."' $selected>".htmlspecialchars($row['job_title'])."</option>";
            }
            ?>
        </select>

        <label>Location:</label>
        <select name="location">
            <option value="">-- Any --</option>
            <?php
            $location_result = $mysqli->query("SELECT DISTINCT location FROM job_listings");
            while ($row = $location_result->fetch_assoc()) {
                $selected = ($_GET['location'] ?? '') == $row['location'] ? 'selected' : '';
                echo "<option value='".htmlspecialchars($row['location'])."' $selected>".htmlspecialchars($row['location'])."</option>";
            }
            ?>
        </select>

        <label>Category:</label>
        <select name="category">
            <option value="">-- Any --</option>
            <?php
            $cat_result = $mysqli->query("SELECT DISTINCT category FROM job_listings");
            while ($row = $cat_result->fetch_assoc()) {
                $selected = ($_GET['category'] ?? '') == $row['category'] ? 'selected' : '';
                echo "<option value='".htmlspecialchars($row['category'])."' $selected>".htmlspecialchars($row['category'])."</option>";
            }
            ?>
        </select>

        <label>Salary Range:</label>
        <input type="number" name="min_salary" placeholder="Min" value="<?= htmlspecialchars($_GET['min_salary'] ?? '') ?>">
        -
        <input type="number" name="max_salary" placeholder="Max" value="<?= htmlspecialchars($_GET['max_salary'] ?? '') ?>">

        <input type="submit" value="Apply Filters" class="btn">
    </form>
</div>

<script>
    function toggleFilter() {
        const form = document.getElementById("filterForm");
        form.style.display = form.style.display === "none" ? "block" : "none";
    }

    // Highlight elements when mouse is near
    document.addEventListener('mousemove', function(e) {
        const elements = document.querySelectorAll('.job-container, .btn');
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
        if (e.target.classList.contains('job-container') || e.target.closest('.job-container')) {
            const job = e.target.classList.contains('job-container') ? e.target : e.target.closest('.job-container');
            job.style.transform = 'scale(0.98)';
            setTimeout(() => {
                job.style.transform = '';
            }, 200);
        }
    });
</script>

<?php
$sql = "SELECT jl.*, ep.company 
        FROM job_listings jl 
        JOIN employerprofile ep ON jl.employer_id = ep.employer_id 
        WHERE 1=1";

if (!empty($_GET['company'])) {
    $company = $mysqli->real_escape_string($_GET['company']);
    $sql .= " AND ep.company = '$company'";
}
if (!empty($_GET['title'])) {
    $title = $mysqli->real_escape_string($_GET['title']);
    $sql .= " AND jl.job_title = '$title'";
}
if (!empty($_GET['location'])) {
    $location = $mysqli->real_escape_string($_GET['location']);
    $sql .= " AND jl.location = '$location'";
}
if (!empty($_GET['category'])) {
    $category = $mysqli->real_escape_string($_GET['category']);
    $sql .= " AND jl.category = '$category'";
}
if (!empty($_GET['min_salary'])) {
    $min_salary = (int)$_GET['min_salary'];
    $sql .= " AND jl.salary >= $min_salary";
}
if (!empty($_GET['max_salary'])) {
    $max_salary = (int)$_GET['max_salary'];
    $sql .= " AND jl.salary <= $max_salary";
}

$result = $mysqli->query($sql);

if ($result && $result->num_rows > 0) {
    while ($row = $result->fetch_assoc()) {
        $job_id = $row['job_id'];

        $stmt = $mysqli->prepare("SELECT * FROM jobapplication WHERE candidate_id = ? AND job_id = ?");
        $stmt->bind_param("ii", $user_id, $job_id);
        $stmt->execute();
        $applied_result = $stmt->get_result();

        echo "<div class='job-container'>";
        echo "<div class='job-info'>";
        echo "<strong>Company:</strong> " . htmlspecialchars($row['company']) . "<br>";
        echo "<strong>Job Title:</strong> " . htmlspecialchars($row['job_title']) . "<br>";
        echo "<strong>Description:</strong> " . htmlspecialchars($row['description']) . "<br>";
        echo "<strong>Location:</strong> " . htmlspecialchars($row['location']) . "<br>";
        echo "<strong>Salary:</strong> $" . htmlspecialchars($row['salary']) . "<br>";
        echo "<strong>Category:</strong> " . htmlspecialchars($row['category']) . "<br>";
        echo "</div>";

        if ($applied_result->num_rows > 0) {
            echo "<button class='applied-btn' disabled>Applied</button>";
        } else {
            echo "<form action='apply.php' method='get'>";
            echo "<input type='hidden' name='job_id' value='" . htmlspecialchars($job_id) . "'>";
            echo "<input type='submit' value='Apply Job' class='btn'>";
            echo "</form>";
        }

        echo "</div>";
    }
} else {
    echo "<p style='text-align: center; font-weight: bold; color: var(--primary-blue);'>No job listings found.</p>";
}

$mysqli->close();
?>

</body>
</html>