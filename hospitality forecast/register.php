<?php
// Connect to database
$conn = new mysqli("localhost", "root", "", "hospitality forecast");
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $name = trim($_POST['name']);
    $password = $_POST['password'];

    // Hash the password
    $hashedPassword = password_hash($password, PASSWORD_BCRYPT);

    // Insert into database
    $stmt = $conn->prepare("INSERT INTO admins (Name, Password) VALUES (?, ?)");
    $stmt->bind_param("ss", $name, $hashedPassword);

    if ($stmt->execute()) {
        echo "<p>Registration successful. You can now <a href='login.html'>login</a>.</p>";
    } else {
        echo "<p>Error: " . $stmt->error . "</p>";
    }

    $stmt->close();
}
?>
