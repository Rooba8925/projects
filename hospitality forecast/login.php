<?php
session_start();

// Connect to database
$conn = new mysqli("localhost", "root", "", "hospitality forecast");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    $stmt = $conn->prepare("SELECT Password FROM admins WHERE LOWER(Name) = LOWER(?)");
    $stmt->bind_param("s", $name);
    $stmt->execute();
    $stmt->bind_result($hashedPassword);

    if ($stmt->fetch()) {
        if (password_verify($password, $hashedPassword)) {
            $_SESSION['user'] = $name;
            // Redirect to the dashboard
            header("Location: hospitality forecast dashboard.php");
            exit();
        } else {
            echo "<p>Invalid password.</p>";
        }
    } else {
        echo "<p>User not found.</p>";
    }

    $stmt->close();
}
?>
