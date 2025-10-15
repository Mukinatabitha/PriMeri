<?php
include 'conf.php';
require '../vendor/autoload.php';
require 'sendMail.php'; // has passwordResetEmail()

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        die("Error: Please enter your email address.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    // Optional: check if email exists in your database
    include 'connect.php';
    $check = $conn->prepare("SELECT name FROM users WHERE email = ?");
    $check->bind_param("s", $email);
    $check->execute();
    $result = $check->get_result();

    if ($result->num_rows === 0) {
        die("Error: No account found with that email address.");
    }

    $user = $result->fetch_assoc();
    $name = $user['name'] ?? 'User';
    $check->close();

    // Call the password reset email function
    $result = passwordResetEmail($email);

    if ($result['status'] === 'success') {
        echo "<script>alert('A password reset code has been sent to your email.'); 
              window.location.href='../html/reset-code.html';</script>";
        exit();
    } else {
        echo "Error: " . $result['message'];
    }
} else {
    // Direct access (no POST)
    header("Location: ../html/forgot-password.html");
    exit();
}
?>
