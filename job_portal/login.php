<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email_id = trim($_POST['email_id']);
    $password = trim($_POST['password']);

    if (empty($email_id) || empty($password)) {
        $error = "Email and Password are required.";
    } else {
        $stmt = $conn->prepare("SELECT user_id, password, role FROM users WHERE email_id = ?");
        if (!$stmt) {
            $error = "Prepare failed: " . $conn->error;
        } else {
            $stmt->bind_param("s", $email_id);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $role = strtolower(trim($row['role'])); // Store role as lowercase

                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['role'] = $role; // Store role as lowercase

                    if ($role === "employer") {
                        header("Location: employer_dashboard.php");
                        exit;
                    } elseif ($role === "job seeker") {
                        header("Location: jobseeker_dashboard.php");
                        exit;
                    } else {
                        $error = "Unknown role: " . htmlspecialchars($role);
                    }
                } else {
                    $error = "Invalid password.";
                }
            } else {
                $error = "No account found.";
            }

            $stmt->close();
        }
    }

    $conn->close();
}
?>
