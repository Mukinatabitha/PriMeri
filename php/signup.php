<?php
include 'connect.php';
require 'sendMail.php';
require 'user.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $accountType = $_POST['accountType'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        die("Error: Passwords do not match.");
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }

    $userObj = new User($db);
    if ($userObj->checkEmailExists($email)) {
        die("Error: Email already registered.");
    }

    if ($userObj->signup($fullName, $email, $accountType, $password)) {
        //send registration email
        // Initialize mailer
$mail = new Mail(
    SMTP_HOST,
    SMTP_PORT,
    SMTP_USERNAME,
    SMTP_PASSWORD,
    SMTP_ENCRYPTION
);

        $mail->registrationEmail($email, $fullName);
        if ($accountType === 'buyer') {
            header("Location: ../html/login.html");
            exit();
        }
        else {
            header("Location: ../html/create_store.html");
            exit();
        }
    } else {
        echo "Error: Registration failed.";
    }
}
?>
