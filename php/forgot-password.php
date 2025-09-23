<?php
header('Content-Type: application/json');

include 'conf.php';
require '../vendor/autoload.php';
require 'email_functions.php'; // your functions

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';

    // Call the password reset function
    $result = passwordResetEmail($email);

    // Return JSON response
    echo json_encode($result);
    exit;
} else {
    echo json_encode([
        'status'  => 'error',
        'message' => 'Invalid request method.'
    ]);
    exit;
}
?>