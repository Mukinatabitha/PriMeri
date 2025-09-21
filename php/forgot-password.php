<?php

// Check for form submission
if ($_SERVER["REQUEST_METHOD"] === "POST") {

    // Retrieve and sanitize the email address
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);

    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {

        //Generate code
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

        // Set the Content-type header for HTML email
        $headers = "MIME-Version: 1.0" . "\r\n";
        $headers .= "Content-type:text/html;charset=UTF-8" . "\r\n";
        $headers .= 'From: PriMeri <noreply@primeri.com>' . "\r\n";

        $mailSent = mail($to, $subject, $message, $headers);

        if ($mailSent) {
            $result = "A verification code has been sent to your email address.";
            $success_class = 'successful';
        } else {
            $result = "Failed to send the email. Please try again later.";
            $success_class = 'error';
        }

    } else {
        $result = "Invalid email address.";
        $success_class = 'error';
    }
}

?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <title>PriMeri - Forgot Password</title>
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <link rel="icon" type="image/png" href="../images/favicon.ico">
  <link rel="stylesheet" href="../css/forgot-password.css">
  <style>
    .success { color: green; }
    .error { color: red; }
  </style>
</head>
<body>
  <div class="main-content">
    <div class="form-section">
      <h2>Reset your password</h2>
      <p>Enter your email address, and we’ll send you a verification code.</p>
      
      <form method="POST" action="">
        <input type="email" id="email" name="email" placeholder="Email address" required>
        <button type="submit">Send Code</button>
      </form>

      <?php if (isset($result)): ?>
        <div id="result">
          <p class="<?php echo $success_class; ?>"><?php echo $result; ?></p>
        </div>
      <?php endif; ?>

      <p class="back-login">Remember your password? <a href="login.html">Log in</a></p>
    </div>
  </div>

  <p class="slogan">Bridging the Gap, Building the Future</p>
</body>
</html>