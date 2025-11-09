<?php
// contact-handler.php
use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

require '../vendor/autoload.php'; // Path to PHPMailer autoload
include 'conf.php'; // contains SMTP_* constants

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Get and sanitize form data
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Validate required fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header("Location: ../html/contact.php?status=empty");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../html/contact.php?status=error");
        exit();
    }

    try {
        $mail = new PHPMailer(true);

        // Server settings
        $mail->isSMTP();
        $mail->Host = SMTP_HOST;
        $mail->SMTPAuth = true;
        $mail->Username = SMTP_USERNAME;
        $mail->Password = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
         $mail->Port = SMTP_PORT;

        // Recipients
        $mail->setFrom($email, 'PriMeri Contact Form');
        $mail->addAddress('maxwell.muthumbi@strathmore.edu', 'PriMeri Team'); // Where to receive emails
        $mail->addReplyTo($email, $name);

        // Content
        $mail->isHTML(true);
        $mail->Subject = "PriMeri Contact: " . $subject;
        
        $mail->Body = "
            <h2>New Contact Form Submission</h2>
            <p><strong>Name:</strong> $name</p>
            <p><strong>Email:</strong> $email</p>
            <p><strong>Subject:</strong> $subject</p>
            <p><strong>Message:</strong></p>
            <p>" . nl2br(htmlspecialchars($message)) . "</p>
            <p><em>Submitted: " . date('Y-m-d H:i:s') . "</em></p>
        ";

        $mail->AltBody = "Name: $name\nEmail: $email\nSubject: $subject\nMessage: $message\nSubmitted: " . date('Y-m-d H:i:s');

        // Send email
        $mail->send();

        // Success
        header("Location: ../html/contact.php");
        exit();

    } catch (Exception $e) {
        // Log error (check your server error logs)
        error_log("Contact form error: " . $e->getMessage());
        
        // Redirect to error page
        header("Location: ../html/contact.php?status=error");
        exit();
    }
} else {
    // If not POST request, redirect
    header("Location: ../html/contact.php");
    exit();
}