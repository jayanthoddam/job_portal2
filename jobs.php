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

$check_jobs = $conn->query("SELECT COUNT(*) as total FROM jobs");
$count = $check_jobs->fetch_assoc()['total'];

if ($count == 0) {
    $conn->query("INSERT INTO jobs (title, description) VALUES 
        ('Web Developer', 'Build and maintain websites and web apps.'),
        ('Graphic Designer', 'Create visual content for branding and marketing.'),
        ('Data Analyst', 'Interpret data and turn it into insights.'),
        ('Digital Marketer', 'Manage online campaigns and SEO strategy.')");
}

$jobs = $conn->query("SELECT * FROM jobs");
?>

<!DOCTYPE html>
<html>
<head>
    <title>Job Listings</title>
    <style>
        @import url('https://fonts.googleapis.com/css2?family=Roboto&display=swap');

        body, html {
            margin: 0;
            padding: 0;
            font-family: 'Roboto', sans-serif;
            height: 100%;
            background: url('https://images.unsplash.com/photo-1504384308090-c894fdcc538d?auto=format&fit=crop&w=1470&q=80') no-repeat center center fixed;
            background-size: cover;
            color: #fff;
        }

        .container {
            max-width: 800px;
            margin: 60px auto;
            background-color: rgba(0, 0, 0, 0.75);
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 0 15px rgba(0,0,0,0.5);
        }

        h1, h2 {
            color: #00bcd4;
            text-align: center;
        }

        .logout-btn {
            display: inline-block;
            padding: 10px 20px;
            background-color: #f44336;
            color: #fff;
            border-radius: 8px;
            text-decoration: none;
            float: right;
            margin-top: -60px;
        }

        .logout-btn:hover {
            background-color: #d32f2f;
        }

        .job-list {
            list-style: none;
            padding: 0;
        }

        .job-list li {
            background-color: rgba(255, 255, 255, 0.05);
            padding: 20px;
            margin: 15px 0;
            border-radius: 10px;
            border-left: 5px solid #00bcd4;
        }

        .job-list h3 {
            margin-top: 0;
            color: #ffc107;
        }

        .job-list p {
            margin: 10px 0;
        }

        .apply-btn {
            display: inline-block;
            margin-top: 10px;
            padding: 10px 15px;
            background-color: #00bcd4;
            color: #fff;
            text-decoration: none;
            border-radius: 5px;
        }

        .apply-btn:hover {
            background-color: #0097a7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Welcome, <?php echo htmlspecialchars($_SESSION['username']); ?></h1>
        <a href="logout.php" class="logout-btn">Logout</a>
        <h2>Available Jobs</h2>
        <?php if ($jobs->num_rows > 0): ?>
            <ul class="job-list">
                <?php while ($job = $jobs->fetch_assoc()): ?>
                    <li>
                        <h3><?php echo htmlspecialchars($job['title']); ?></h3>
                        <p><?php echo htmlspecialchars($job['description']); ?></p>
                        <a class="apply-btn" href="apply_jobs.php?job_id=<?php echo $job['id']; ?>">Apply</a>
                    </li>
                <?php endwhile; ?>
            </ul>
        <?php else: ?>
            <p>No jobs available right now.</p>
        <?php endif; ?>
    </div>
</body>
</html>
