<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit();
}

$conn = new mysqli('localhost', 'root', '', 'job_portal2');
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$user_id = $_SESSION['user_id'];

if (!isset($_GET['job_id'])) {
    die("Job ID missing.");
}

$job_id = (int)$_GET['job_id'];

$job_result = $conn->query("SELECT * FROM jobs WHERE id = $job_id");
if ($job_result->num_rows == 0) {
    die("Job not found.");
}

$job = $job_result->fetch_assoc();
?>

<!DOCTYPE html>
<html>
<head>
    <title>Apply for Job</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

        body, html {
            height: 100%;
            margin: 0;
            font-family: 'Roboto', sans-serif;
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .container {
            max-width: 600px;
            background-color: rgba(0, 0, 0, 0.75);
            margin: 80px auto;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.7);
            text-align: center;
        }

        h2 {
            color: #00bcd4;
            margin-bottom: 15px;
        }

        p {
            font-size: 1.1em;
            margin-bottom: 30px;
        }

        form button {
            background-color: #00bcd4;
            border: none;
            color: white;
            padding: 12px 25px;
            font-size: 1.1em;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
        }

        form button:hover {
            background-color: #0097a7;
        }

        a {
            color: #00bcd4;
            text-decoration: none;
            font-weight: 600;
            display: inline-block;
            margin-top: 25px;
            transition: color 0.3s ease;
        }

        a:hover {
            color: #0097a7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Apply for: <?php echo htmlspecialchars($job['title']); ?></h2>
        <p><?php echo htmlspecialchars($job['description']); ?></p>

        <form method="POST" action="apply.php?job_id=<?php echo $job_id; ?>">
            <button type="submit">Apply Now</button>
        </form>

        <a href="jobs.php">Back to job listings</a>
    </div>
</body>
</html>
