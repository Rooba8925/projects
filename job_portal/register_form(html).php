<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Register</title>
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
            background-color: #0072ff;
        }

        /* Blurred background image with blue overlay */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background-image: url('https://img.freepik.com/premium-photo/hrhuman-resourcesmanagement-concept-hr-human-resources-management-document-data-management-technology-resource-perfect-employee-business_10541-9056.jpg?w=1380');
            background-size: cover;
            background-position: center;
            filter: blur(4px) brightness(0.8);
            opacity: 0.9;
            z-index: -1;
        }

        /* Blue bubbles background */
        .bubbles {
            position: absolute;
            width: 100%;
            height: 100%;
            z-index: 0;
            overflow: hidden;
            top: 0;
            left: 0;
        }

        .bubble {
            position: absolute;
            bottom: -100px;
            background: rgba(0, 114, 255, 0.3);
            border-radius: 50%;
            opacity: 0.5;
            animation: rise 15s infinite ease-in;
        }

        .bubble:nth-child(1) {
            width: 40px;
            height: 40px;
            left: 10%;
            animation-duration: 8s;
        }

        .bubble:nth-child(2) {
            width: 20px;
            height: 20px;
            left: 20%;
            animation-duration: 5s;
            animation-delay: 1s;
        }

        .bubble:nth-child(3) {
            width: 50px;
            height: 50px;
            left: 35%;
            animation-duration: 7s;
            animation-delay: 2s;
        }

        .bubble:nth-child(4) {
            width: 80px;
            height: 80px;
            left: 50%;
            animation-duration: 11s;
            animation-delay: 0s;
        }

        .bubble:nth-child(5) {
            width: 35px;
            height: 35px;
            left: 55%;
            animation-duration: 6s;
            animation-delay: 1s;
        }

        .bubble:nth-child(6) {
            width: 45px;
            height: 45px;
            left: 65%;
            animation-duration: 8s;
            animation-delay: 3s;
        }

        .bubble:nth-child(7) {
            width: 25px;
            height: 25px;
            left: 75%;
            animation-duration: 7s;
            animation-delay: 2s;
        }

        .bubble:nth-child(8) {
            width: 80px;
            height: 80px;
            left: 80%;
            animation-duration: 6s;
            animation-delay: 1s;
        }

        @keyframes rise {
            0% {
                bottom: -100px;
                transform: translateX(0);
            }
            50% {
                transform: translateX(100px);
            }
            100% {
                bottom: 1080px;
                transform: translateX(-200px);
            }
        }

        .form-container {
            background-color: rgba(255, 255, 255, 0.95);
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 10px 30px rgba(0, 114, 255, 0.3);
            width: 400px;
            z-index: 1;
            position: relative;
            overflow: hidden;
            border: 2px solid rgba(0, 114, 255, 0.3);
            transition: all 0.3s ease;
        }

        .form-container:hover {
            box-shadow: 0 15px 35px rgba(0, 114, 255, 0.4);
            transform: translateY(-5px);
        }

        .form-header {
            display: flex;
            flex-direction: column;
            align-items: center;
            margin-bottom: 25px;
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 15px;
            transition: all 0.3s ease;
        }

        .logo:hover {
            transform: scale(1.1) rotate(10deg);
        }

        h2 {
            text-align: center;
            margin: 0;
            color: #0072ff;
            font-size: 28px;
            font-weight: 700;
            text-shadow: 0 2px 4px rgba(0, 114, 255, 0.2);
        }

        label {
            font-weight: 600;
            display: block;
            margin-top: 15px;
            color: #333;
            transition: all 0.3s ease;
        }

        input, select {
            width: 100%;
            padding: 12px 15px;
            margin-top: 8px;
            border: 2px solid rgba(0, 114, 255, 0.3);
            border-radius: 8px;
            font-size: 16px;
            transition: all 0.3s ease;
            background-color: rgba(255, 255, 255, 0.8);
        }

        input:focus, select:focus {
            outline: none;
            border-color: #0072ff;
            box-shadow: 0 0 0 3px rgba(0, 114, 255, 0.2);
            background-color: white;
        }

        input:hover, select:hover {
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

        button::after {
            content: "";
            position: absolute;
            top: 50%;
            left: 50%;
            width: 5px;
            height: 5px;
            background: rgba(255, 255, 255, 0.5);
            opacity: 0;
            border-radius: 100%;
            transform: scale(1, 1) translate(-50%);
            transform-origin: 50% 50%;
        }

        button:focus:not(:active)::after {
            animation: ripple 1s ease-out;
        }

        @keyframes ripple {
            0% {
                transform: scale(0, 0);
                opacity: 0.5;
            }
            100% {
                transform: scale(20, 20);
                opacity: 0;
            }
        }

        .back-button {
            background: linear-gradient(135deg, #6c757d, #adb5bd);
            margin-top: 15px;
        }

        .back-button:hover {
            background: linear-gradient(135deg, #5a6268, #868e96);
        }

        .employer-fields {
            display: none;
            animation: fadeIn 0.5s ease;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(-10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        .form-container::before {
            content: "";
            position: absolute;
            top: -10px;
            left: -10px;
            right: -10px;
            bottom: -10px;
            border: 2px solid rgba(0, 114, 255, 0.1);
            border-radius: 25px;
            z-index: -1;
            animation: pulse 4s infinite;
        }

        @keyframes pulse {
            0% { border-color: rgba(0, 114, 255, 0.1); }
            50% { border-color: rgba(0, 114, 255, 0.3); }
            100% { border-color: rgba(0, 114, 255, 0.1); }
        }
    </style>
    <script>
        function toggleEmployerFields() {
            const role = document.querySelector("select[name='role']").value;
            const employerFields = document.getElementById("employerFields");
            
            if (role.toLowerCase() === "employer") {
                employerFields.style.display = "block";
                setTimeout(() => {
                    employerFields.style.opacity = "1";
                }, 10);
            } else {
                employerFields.style.opacity = "0";
                setTimeout(() => {
                    employerFields.style.display = "none";
                }, 300);
            }
        }

        window.onload = function() {
            toggleEmployerFields();
            
            // Add ripple effect to buttons
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
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
            });
        };
    </script>
</head>
<body>
    <div class="bubbles">
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
        <div class="bubble"></div>
    </div>

    <div class="form-container">
        <div class="form-header">
            <img src="https://cdn-icons-png.flaticon.com/128/15189/15189159.png" alt="Logo" class="logo">
            <h2>Create Account</h2>
        </div>
        <form action="register.php" method="POST">
            <label>Name:</label>
            <input type="text" name="name" required>

            <label>Email:</label>
            <input type="email" name="email_id" required>

            <label>Password:</label>
            <input type="password" name="password" required>

            <label>Role:</label>
            <select name="role" required onchange="toggleEmployerFields()">
                <option value="job seeker">Job Seeker</option>
                <option value="employer">Employer</option>
            </select>

            <div id="employerFields" class="employer-fields">
                <label>Company Name:</label>
                <input type="text" name="company">

                <label>Contact Info:</label>
                <input type="text" name="contact_info">
            </div>

            <button type="submit">Register Now</button>
            <button type="button" class="back-button" onclick="window.history.back(); return false;">Back</button>
        </form>
    </div>

       <script>
        function toggleEmployerFields() {
            const role = document.querySelector("select[name='role']").value;
            const employerFields = document.getElementById("employerFields");

            if (role.toLowerCase() === "employer") {
                employerFields.style.display = "block";
                setTimeout(() => {
                    employerFields.style.opacity = "1";
                }, 10);
            } else {
                employerFields.style.opacity = "0";
                setTimeout(() => {
                    employerFields.style.display = "none";
                }, 300);
            }
        }

        window.onload = function() {
            toggleEmployerFields();
            
            const buttons = document.querySelectorAll('button');
            buttons.forEach(button => {
                button.addEventListener('click', function(e) {
                    const x = e.clientX - e.target.getBoundingClientRect().left;
                    const y = e.clientY - e.target.getBoundingClientRect().top;

                    const ripple = document.createElement('span');
                    ripple.className = 'ripple-effect';
                    ripple.style.left = x + 'px';
                    ripple.style.top = y + 'px';

                    this.appendChild(ripple);

                    setTimeout(() => {
                        ripple.remove();
                    }, 1000);
                });
            });
        };
    </script>
</body>
</html>