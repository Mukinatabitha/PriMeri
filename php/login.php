<?php
session_start();
include 'connect.php';
require 'user.php';

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

    $userObj = new User($db);
    $user = $userObj->login($email, $password);

    if ($user) {
        // Login successful - store user info temporarily
        $_SESSION['pending_2fa'] = true;
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_email'] = $user['email'];
        $_SESSION['account_type'] = $user['accountType'];

        // Redirect to two-factor verification page first
        if ($user['accountType'] === 'buyer') {
        header("Location: ../html/home.html");
        exit();
        } else {
        header("Location: ../html/my_store.html");
        exit();
        }
    } else {
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../html/login.html");
        exit();
    }
}

$db->closeConnection();
?>

