<?php
require 'config.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $email = trim($_POST['email_id']);
    $password = trim($_POST['password']);
    $role = strtolower(trim($_POST['role'])); // Ensure lowercase for ENUM match

    if (empty($role)) {
        die("Role is required.");
    }

    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    $stmt = $conn->prepare("INSERT INTO users (name, email_id, password, role) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $name, $email, $hashedPassword, $role);

    if ($stmt->execute()) {
        $user_id = $stmt->insert_id;

        if ($role === 'employer') {
            $company = isset($_POST['company']) ? trim($_POST['company']) : '';
            $contact_info = isset($_POST['contact_info']) ? trim($_POST['contact_info']) : '';

            if (empty($company) || empty($contact_info)) {
                die("Company name and contact info required for employer.");
            }

            $stmt2 = $conn->prepare("INSERT INTO employerprofile (user_id, name, company, contact_info) VALUES (?, ?, ?, ?)");
            $stmt2->bind_param("isss", $user_id, $name, $company, $contact_info);
            if (!$stmt2->execute()) {
                die("Employer profile insert failed: " . $stmt2->error);
            }
            $stmt2->close();
        }

        header("Location: login(html).php");
        exit();
    } else {
        die("User insert failed: " . $stmt->error);
    }

    $stmt->close();
    $conn->close();
}
?>
