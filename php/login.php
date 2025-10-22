<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../html/login.html");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../html/login.html");
        exit();
    }

    // Check if user exists and verify password
    $stmt = $conn->prepare("SELECT id, name, email, password, accountType FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        if (password_verify($password, $user['password'])) {
            // Login successful - store user info temporarily
            $_SESSION['pending_2fa'] = true;
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['account_type'] = $user['accountType'];

            // Redirect to two-factor verification page first
            header("Location: ../html/twofactor.html");
            exit();
        } else {
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../html/login.html");
            exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../html/login.html");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>

