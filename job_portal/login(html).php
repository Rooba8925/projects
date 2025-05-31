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
                $role = strtolower(trim($row['role']));

                if (password_verify($password, $row['password'])) {
                    $_SESSION['user_id'] = $row['user_id'];
                    $_SESSION['role'] = ucfirst($role);

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

<!DOCTYPE html>
<html>
<head>
    <title>Login</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            display: flex;
            justify-content: center;
            align-items: center;
            height: 100vh;
            position: relative;
            overflow: hidden;
            background-color: rgba(0, 114, 255, 0.1);
        }

        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-image: url('https://img.freepik.com/premium-vector/log-people-isometric-vector-illustration-illustration-with-sign-people-mobile-app-design_123447-4034.jpg?w=996');
            background-size: cover;
            background-position: center;
            filter: blur(3px) brightness(0.8);
            opacity: 0.9;
            z-index: -1;
        }

        .login-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 10px 30px rgba(0, 114, 255, 0.3);
            width: 350px;
            z-index: 1;
            border: 2px solid rgba(0, 114, 255, 0.2);
            transition: all 0.3s ease;
            position: relative;
            overflow: hidden;
        }

        .login-container:hover {
            box-shadow: 0 15px 35px rgba(0, 114, 255, 0.4);
            transform: translateY(-5px);
        }

        .login-container::before {
            content: "";
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid rgba(0, 114, 255, 0.1);
            border-radius: 15px;
            z-index: -1;
            animation: pulse 4s infinite;
        }

        @keyframes pulse {
            0% { border-color: rgba(0, 114, 255, 0.1); }
            50% { border-color: rgba(0, 114, 255, 0.3); }
            100% { border-color: rgba(0, 114, 255, 0.1); }
        }

        .logo-container {
            text-align: center;
            margin-bottom: 20px;
        }

        .logo {
            width: 80px;
            height: 80px;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.1) rotate(5deg);
            filter: drop-shadow(0 0 8px rgba(0, 114, 255, 0.4));
        }

        h2 {
            text-align: center;
            color: #0072ff;
            margin-bottom: 25px;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 114, 255, 0.1);
            margin-top: 10px;
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
            color: #333;
            transition: all 0.3s ease;
        }

        input[type="email"],
        input[type="password"] {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            border: 2px solid rgba(0, 114, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }

        input[type="email"]:focus,
        input[type="password"]:focus {
            outline: none;
            border-color: #0072ff;
            box-shadow: 0 0 0 3px rgba(0, 114, 255, 0.2);
            background-color: white;
        }

        input[type="email"]:hover,
        input[type="password"]:hover {
            border-color: #0072ff;
        }

        button {
            width: 100%;
            margin-top: 25px;
            padding: 14px;
            background: linear-gradient(135deg, #0072ff, #00c6ff);
            color: white;
            border: none;
            font-size: 18px;
            font-weight: 600;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(0, 114, 255, 0.2);
            position: relative;
            overflow: hidden;
        }

        button:hover {
            background: linear-gradient(135deg, #0062d6, #00b4e6);
            box-shadow: 0 6px 12px rgba(0, 114, 255, 0.3);
            transform: translateY(-2px);
        }

        button:active {
            transform: translateY(0);
            box-shadow: 0 2px 4px rgba(0, 114, 255, 0.3);
        }

        .error {
            color: #ff4757;
            text-align: center;
            margin-top: 10px;
            padding: 10px;
            background-color: rgba(255, 71, 87, 0.1);
            border-radius: 6px;
            border-left: 4px solid #ff4757;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-footer {
            text-align: center;
            margin-top: 20px;
            color: #666;
        }

        .form-footer a {
            color: #0072ff;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .form-footer a:hover {
            color: #0056b3;
            text-decoration: underline;
        }

        .back-button {
            display: inline-block;
            margin-top: 12px;
            padding: 10px 18px;
            background-color: #e0f0ff;
            color: #0072ff;
            border: 2px solid #0072ff;
            border-radius: 6px;
            font-weight: 600;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .back-button:hover {
            background-color: #0072ff;
            color: white;
            text-decoration: none;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="logo-container">
            <img src="https://cdn-icons-png.flaticon.com/128/15189/15189159.png" alt="Logo" class="logo">
        </div>
        <h2>Login</h2>
        <?php if (!empty($error)): ?>
            <div class="error"><?php echo $error; ?></div>
        <?php endif; ?>
        <form method="POST">
            <label>Email:</label>
            <input type="email" name="email_id" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <button type="submit">Login</button>
        </form>
        <div class="form-footer">
            Don't have an account? <a href="register_form(html).php">Sign up</a><br><br>
            <a href="index.php" class="back-button">← Back to Home</a>
        </div>
    </div>

    <script>
        document.querySelector('button').addEventListener('click', function(e) {
            const x = e.clientX - e.target.getBoundingClientRect().left;
            const y = e.clientY - e.target.getBoundingClientRect().top;

            const ripple = document.createElement('span');
            ripple.className = 'ripple-effect';
            ripple.style.left = `${x}px`;
            ripple.style.top = `${y}px`;

            this.appendChild(ripple);

            setTimeout(() => {
                ripple.remove();
            }, 1000);
        });

        const inputs = document.querySelectorAll('input');
        inputs.forEach(input => {
            input.addEventListener('focus', function() {
                this.parentNode.querySelector('label').style.color = '#0072ff';
                this.parentNode.querySelector('label').style.fontWeight = '700';
            });

            input.addEventListener('blur', function() {
                this.parentNode.querySelector('label').style.color = '#333';
                this.parentNode.querySelector('label').style.fontWeight = '600';
            });
        });
    </script>

    <style>
        .ripple-effect {
            position: absolute;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.4);
            transform: scale(0);
            animation: ripple 0.6s linear;
            pointer-events: none;
        }

        @keyframes ripple {
            to {
                transform: scale(2.5);
                opacity: 0;
            }
        }
    </style>
</body>
</html>
