<?php
session_start();

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $conn = new mysqli('localhost', 'root', '', 'job_portal2');

    if ($conn->connect_error) {
        die("<div class='error-box'>Connection failed: " . $conn->connect_error . "</div>");
    }

    $username = $conn->real_escape_string(trim($_POST['username']));
    $email = $conn->real_escape_string(trim($_POST['email']));
    $password = $_POST['password'];

    if (strlen($password) < 6) {
        die("<div class='error-box'>Password must be at least 6 characters. <a href='register.php'>Go back</a></div>");
    }

    $check = $conn->query("SELECT id FROM users WHERE email='$email' OR username='$username'");
    if (!$check) {
        die("<div class='error-box'>Query Error: " . $conn->error . "</div>");
    }

    if ($check->num_rows > 0) {
        die("<div class='error-box'>Username or email already taken. <a href='register.php'>Go back</a></div>");
    }

    $hashed_password = password_hash($password, PASSWORD_DEFAULT);

    $sql = "INSERT INTO users (username, email, password) VALUES ('$username', '$email', '$hashed_password')";
    if ($conn->query($sql) === TRUE) {
        $_SESSION['user_id'] = $conn->insert_id;
        $_SESSION['username'] = $username;
        header("Location: jobs.php");
        exit();
    } else {
        die("<div class='error-box'>Error: " . $conn->error . "</div>");
    }
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Register - Job Portal</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            height: 100%;
            font-family: 'Roboto', sans-serif;
            background: url('https://images.unsplash.com/photo-1498050108023-c5249f4df085?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
        }

        .container {
            background-color: rgba(0, 0, 0, 0.75);
            max-width: 400px;
            margin: 80px auto;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0, 0, 0, 0.8);
            color: #ffffff;
        }

        h2 {
            text-align: center;
            color: #00bcd4;
            margin-bottom: 20px;
        }

        input[type="text"], input[type="email"], input[type="password"] {
            width: 100%;
            padding: 12px;
            margin: 10px 0;
            border: none;
            border-radius: 8px;
            font-size: 16px;
        }

        button {
            width: 100%;
            padding: 12px;
            background-color: #00bcd4;
            color: #fff;
            border: none;
            font-size: 16px;
            border-radius: 8px;
            cursor: pointer;
            margin-top: 10px;
        }

        button:hover {
            background-color: #0097a7;
        }

        .error-box {
            background-color: #f44336;
            color: white;
            padding: 12px;
            margin: 20px auto;
            max-width: 400px;
            border-radius: 8px;
            text-align: center;
        }

        p {
            text-align: center;
            margin-top: 15px;
        }

        a {
            color: #ffc107;
            text-decoration: none;
            font-weight: bold;
        }

        a:hover {
            text-decoration: underline;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Register</h2>
        <form method="POST" action="signup.php">
            <input type="text" name="username" placeholder="Username" required>
            <input type="email" name="email" placeholder="Email" required>
            <input type="password" name="password" placeholder="Password (min 6 characters)" required>
            <button type="submit">Register</button>
        </form>
        <p>Already registered? <a href="login.php">Login here</a></p>
    </div>
</body>
</html>
