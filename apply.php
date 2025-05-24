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

$msg = '';
$success = false;

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $username = trim($_POST['username'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $resume = $_FILES['resume'] ?? null;

    
    if (empty($username) || empty($email)) {
        $msg = "Please fill in all required fields.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $msg = "Invalid email address.";
    } elseif (!$resume || $resume['error'] !== UPLOAD_ERR_OK) {
        $msg = "Please upload your resume.";
    } else {
        
        $allowed_types = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        if (!in_array($resume['type'], $allowed_types)) {
            $msg = "Resume must be a PDF or Word document.";
        } else {
            
            $upload_dir = __DIR__ . '/uploads/';
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }

            
            $ext = pathinfo($resume['name'], PATHINFO_EXTENSION);
            $resume_filename = uniqid('resume_') . '.' . $ext;
            $target_path = $upload_dir . $resume_filename;

            if (move_uploaded_file($resume['tmp_name'], $target_path)) {
                
                $applied_on = date('Y-m-d H:i:s');
                $username_esc = $conn->real_escape_string($username);
                $email_esc = $conn->real_escape_string($email);
                $resume_esc = $conn->real_escape_string($resume_filename);

                
                $check = $conn->query("SELECT * FROM applications WHERE user_id=$user_id AND job_id=$job_id");
                if ($check->num_rows > 0) {
                    $msg = "You have already applied for this job.";
                    unlink($target_path);
                } else {
                    $sql = "INSERT INTO applications (user_id, job_id, applied_on, username, email, resume) 
                            VALUES ($user_id, $job_id, '$applied_on', '$username_esc', '$email_esc', '$resume_esc')";
                    if ($conn->query($sql) === TRUE) {
                        $msg = "✅ Your application has been saved successfully! You will receive a confirmation call soon. Please check your email inbox and spam folder.";
                        $success = true;
                    } else {
                        $msg = "Database error: " . $conn->error;
                        unlink($target_path);
                    }
                }
            } else {
                $msg = "Failed to upload resume.";
            }
        }
    }
}
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
            background-color: rgba(0,0,0,0.75);
            margin: 80px auto;
            padding: 30px 40px;
            border-radius: 15px;
            box-shadow: 0 0 20px rgba(0,0,0,0.7);
        }
        h2 {
            color: #00bcd4;
            margin-bottom: 20px;
            text-align: center;
        }
        form {
            display: flex;
            flex-direction: column;
            gap: 20px;
        }
        label {
            font-weight: 600;
            font-size: 1.1em;
        }
        input[type="text"],
        input[type="email"],
        input[type="file"] {
            padding: 10px;
            font-size: 1em;
            border-radius: 8px;
            border: none;
            outline: none;
        }
        input[type="text"]:focus,
        input[type="email"]:focus,
        input[type="file"]:focus {
            box-shadow: 0 0 5px #00bcd4;
        }
        button {
            background-color: #00bcd4;
            border: none;
            color: white;
            padding: 15px;
            font-size: 1.2em;
            border-radius: 8px;
            cursor: pointer;
            transition: background-color 0.3s ease;
            font-weight: 700;
        }
        button:hover {
            background-color: #0097a7;
        }
        .message {
            background-color: #4caf50;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
        .error {
            background-color: #f44336;
            padding: 15px;
            border-radius: 8px;
            font-weight: 600;
            text-align: center;
            margin-bottom: 20px;
        }
        a {
            display: block;
            margin-top: 25px;
            color: #00bcd4;
            font-weight: 600;
            text-align: center;
            text-decoration: none;
        }
        a:hover {
            color: #0097a7;
        }
    </style>
</head>
<body>
    <div class="container">
        <h2>Apply for: <?php echo htmlspecialchars($job['title']); ?></h2>
        <p style="text-align:center;"><?php echo htmlspecialchars($job['description']); ?></p>

        <?php if ($msg): ?>
            <div class="<?php echo $success ? 'message' : 'error'; ?>">
                <?php echo htmlspecialchars($msg); ?>
            </div>
        <?php endif; ?>

        <?php if (!$success): ?>
            <form method="POST" enctype="multipart/form-data">
                <label for="username">Username:</label>
                <input type="text" id="username" name="username" required value="<?php echo htmlspecialchars($_POST['username'] ?? ''); ?>">

                <label for="email">Email:</label>
                <input type="email" id="email" name="email" required value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">

                <label for="resume">Upload Resume (PDF, DOC, DOCX):</label>
                <input type="file" id="resume" name="resume" accept=".pdf,.doc,.docx" required>

                <button type="submit">Submit Application</button>
            </form>
        <?php else: ?>
            <a href="jobs.php">Back to job listings</a>
        <?php endif; ?>
    </div>
</body>
</html>
