<?php
include 'conf.php';
include 'connect.php';
require 'sendMail.php';
require 'user.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);

    if (empty($email)) {
        die("Error: Please enter your email address.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    $userObj = new User($db);
    if (!$userObj->checkEmailExists($email)) {
        die("Error: No account found with that email address.");
    }

    $mail = new Mail(SMTP_HOST, SMTP_PORT, SMTP_USERNAME, SMTP_PASSWORD, SMTP_ENCRYPTION);
    $result = $mail->passwordResetEmail($email);

    if ($result['status'] === 'success') {
        echo "<script>alert('A password reset code has been sent to your email.');
              window.location.href='../html/reset-code.html';</script>";
        exit();
    } else {
        echo "Error: " . $result['message'];
    }
} else {
    // Direct access (no POST)
    header("Location: ../html/reset-password.html");
    exit();
}
?>
