<?php

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // submit the code to the user's email
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

        // Generate code
        $code = random_int(100000, 999999); 

        $to = $email;
        $subject = "Password Reset Code";
        $message = '
        <html>
        <head>
          <title>Password Reset</title>
        </head>
        <body>
          <h2>Hello,</h2>
          <p>You requested a password reset. Your verification code is:</p>
          <h3>' . $code . '</h3>
          <p>This code is valid for a short period of time. Do not share the code with anyone.</p>
          <p>If you did not request this,ignore this email.</p>
        </body>
        </html>
        ';

        
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PriMeri <noreply@primeri.com>' . "\r\n";

        $mailSent = mail($to, $subject, $message, $headers);

        if ($mailSent) {
            // Success response (can be a JSON response in a real-world scenario)
            echo json_encode(['status' => 'success', 'message' => 'A verification code has been sent to your email address.']);
        } else {
            // Error response
            echo json_encode(['status' => 'error', 'message' => 'Failed to send the email. Please try again later.']);
        }

    } else {
        // Invalid email response
        echo json_encode(['status' => 'error', 'message' => 'Invalid email address.']);
    }
}