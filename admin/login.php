<?php
// --- Start session & show errors ---
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

// --- Include your database connection ---
include '../php/connect.php';

// --- Handle login submission ---
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }

    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    if (!$stmt) {
        die("Database error: " . $db->error);
    }

    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();

        // Compare plain text passwords (since your column is 'password')
        if ($password === $admin['Password']) {
            $_SESSION['admin_id'] = $admin['adminID'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PriMeri Admin Login</title>
    <style>
        :root {
            --primary-color: #2f5a2f;
            --primary-dark: #1f3b1f;
            --bg-dark: #ece3d3;
            --bg-darker: #d6cbb8;
            --text-light: #f0f0f0;
            --border-color: #1f3b1f;
        }
        * { margin:0; padding:0; box-sizing:border-box; font-family:'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        body {
            background: linear-gradient(135deg, var(--bg-darker), var(--bg-dark));
            color: var(--text-light);
            min-height: 100vh;
            display: flex;
            justify-content: center;
            align-items: center;
            padding: 20px;
        }
        .login-container {
            background: rgba(0,0,0,0.85);
            width: 100%;
            max-width: 400px;
            padding: 40px 30px;
            border-radius: 10px;
            box-shadow: 0 0 25px rgba(255, 62, 62, 0.3);
            border: 1px solid var(--border-color);
            position: relative;
        }
        .login-title {
            color: var(--bg-dark);
            text-align: center;
            margin-bottom: 30px;
            font-size: 28px;
            text-transform: uppercase;
            letter-spacing: 2px;
            font-weight: 700;
        }
        .input-group { margin-bottom: 25px; }
        .input-group label {
            display: block;
            margin-bottom: 8px;
            font-size: 14px;
            color: #aaa;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
        input {
            width: 100%;
            padding: 12px 15px;
            background: #2c2c2c;
            border: 1px solid #444;
            border-radius: 5px;
            color: var(--text-light);
            font-size: 16px;
        }
        input:focus {
            border-color: var(--primary-color);
        }
        button {
            width: 100%;
            padding: 14px;
            background: var(--primary-color);
            color: white;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
        }
        button:hover {
            background: var(--primary-dark);
        }
        .error-message {
            color: var(--primary-color);
            text-align: center;
            margin-top: 15px;
            font-size: 14px;
            min-height: 20px;
        }
        .security-notice {
            text-align: center;
            margin-top: 25px;
            font-size: 12px;
            color: #666;
            border-top: 1px solid #333;
            padding-top: 15px;
        }
    </style>
</head>
<body>
    <div class="login-container">
        <h1 class="login-title">PriMeri Admin Access</h1>
        <form method="post" action="login.php">
            <div class="input-group">
                <label for="username">Username</label>
                <input type="text" id="username" name="username" placeholder="Enter your username" required>
            </div>
            <div class="input-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" placeholder="Enter your password" required>
            </div>
            <button type="submit">Login</button>

            <div class="error-message">
                <?php
                if (isset($_SESSION['error'])) {
                    echo $_SESSION['error'];
                    unset($_SESSION['error']);
                }
                ?>
            </div>
        </form>
        <div class="security-notice">
            Unauthorized access is strictly prohibited
        </div>
    </div>
</body>
</html>
