<!DOCTYPE html>
<html>
<head>
    <title>Job Portal System</title>
    <style>
        body {
            margin: 0;
            padding: 0;
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            color: white;
            text-align: center;
            height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        /* Blurred background */
        body::before {
            content: "";
            position: absolute;
            top: 0;
            left: 0;
            height: 100%;
            width: 100%;
            background-image: url('https://img.freepik.com/free-vector/online-job-interview_23-2148644500.jpg?t=st=1746867902~exp=1746871502~hmac=09fb5dcfcee6d2137eee44809c5971fccc47cfc671a1b6896407783f8b50c909&w=1380');
            background-size: cover;
            background-position: center;
            filter: blur(2px);
            z-index: -1;
        }

        .container {
            background-color: rgba(128, 0, 128, 0.5); /* Transparent violet */
            padding: 50px 40px;
            border-radius: 16px;
            box-shadow: 0 0 20px rgba(0, 0, 0, 0.4);
        }

        .logo {
            width: 80px;
            height: 80px;
            margin-bottom: 20px;
            transition: all 0.3s ease;
            cursor: pointer;
            filter: drop-shadow(3px 3px 6px rgba(0, 0, 0, 0.6));
        }

        .logo:hover {
            transform: scale(1.1);
            filter: drop-shadow(0 0 10px rgba(255, 255, 255, 0.8)) brightness(1.2);
        }

        .logo:active {
            transform: scale(0.95);
            filter: drop-shadow(0 0 15px rgba(255, 255, 255, 1)) brightness(1.3);
        }

        h1 {
            font-size: 50px;
            font-weight: bold;
            margin-bottom: 40px;
            color: white;
            text-shadow: 3px 3px 6px rgba(0, 0, 0, 0.6);
        }

        .button-group {
            display: flex;
            justify-content: center;
            gap: 30px;
        }

        .btn {
            background-color: white;
            color: #2575fc;
            font-size: 20px;
            padding: 15px 30px;
            border: none;
            border-radius: 8px;
            cursor: pointer;
            font-weight: bold;
            transition: 0.3s ease;
            text-decoration: none;
        }

        .btn:hover {
            background-color: #f1f1f1;
            transform: scale(1.05);
        }
    </style>
</head>
<body>

    <div class="container">
        <img src="https://cdn-icons-png.flaticon.com/128/15189/15189159.png" class="logo" alt="Job Portal Icon">
        <h1>Job Portal System</h1>
        <div class="button-group">
            <a href="register_form(html).php" class="btn">Register Here</a>
            <a href="login(html).php" class="btn">Login Here</a>
        </div>
    </div>

</body>
</html>