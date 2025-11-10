<?php
include '../php/connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = trim($_POST['username']);
    $password = $_POST['password'];

    // Validate input
    if (empty($username) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: login.php");
        exit();
    }

    // Prepare and execute query
    $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
    if (!$stmt) {
        die("Database error: " . $db->error);
    }
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result && $result->num_rows === 1) {
        $admin = $result->fetch_assoc();
        
        // If your passwords are stored as plain text (not recommended):
        if ($password === $admin['password']) {
            // Login successful
            $_SESSION['admin_id'] = $admin['adminID'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }

        // If your passwords are hashed using password_hash(), use this instead:
        /*
        if (password_verify($password, $admin['password'])) {
            $_SESSION['admin_id'] = $admin['adminID'];
            $_SESSION['admin_username'] = $admin['username'];
            header("Location: dashboard.php");
            exit();
        } else {
            $_SESSION['error'] = "Invalid username or password.";
            header("Location: login.php");
            exit();
        }
        */
    } else {
        $_SESSION['error'] = "Invalid username or password.";
        header("Location: login.php");
        exit();
    }
}
?>
