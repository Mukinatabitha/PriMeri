<?php
include 'connect.php'; // connect to database

if ($_SERVER["REQUEST_METHOD"] === "POST") {
    // Sanitize form inputs
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);

    // Check for empty fields
    if (empty($name) || empty($email) || empty($subject) || empty($message)) {
        header("Location: ../html/contact.php?status=empty");
        exit();
    }

    // Validate email format
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        header("Location: ../html/contact.php?status=invalid_email");
        exit();
    }

    // Insert message into database
    $stmt = $conn->prepare("INSERT INTO messages (name, email, subject, message, created_at) VALUES (?, ?, ?, ?, NOW())");
    $stmt->bind_param("ssss", $name, $email, $subject, $message);

    if ($stmt->execute()) {
        // On success, redirect back to the PHP contact page with success status
        header("Location: ../php/contact.php?status=success");
        exit();
    } else {
        // On error
        header("Location: ../php/contact.php?status=error");
        exit();
    }

    $stmt->close();
    $conn->close();
} else {
    // If accessed directly, go back to contact page
    header("Location: ../php/contact.php");
    exit();
}
?>

