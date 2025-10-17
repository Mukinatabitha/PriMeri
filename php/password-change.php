<?php
include 'conf.php';
include 'connect.php';
require '../vendor/autoload.php';
require 'sendMail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;


$host = "localhost";
$user = "root";
$pass = "";
$dbname = "your_database";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) die("Database connection failed.");
$message = "";


// Send Reset Link
if (isset($_POST['action']) && $_POST['action'] === 'send_link') {
    $email = trim($_POST['email'] ?? '');
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "If an account exists for that email, a reset link will be sent.";
    } else {
        // Try to find the user (select only email)
        $stmt = $conn->prepare("SELECT email FROM users WHERE email = ? LIMIT 1");
        if ($stmt === false) {
            $message = "An error occurred. Try again later.";
        } else {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            // Use a generic message to avoid account enumeration
            $message = "If an account exists for that email, a reset link will be sent.";

            if ($result && $result->num_rows === 1) {
                $token = bin2hex(random_bytes(32));
                $expires = date("Y-m-d H:i:s", strtotime('+1 hour'));

                $upd = $conn->prepare("UPDATE users SET reset_token = ?, reset_expires = ? WHERE email = ?");
                if ($upd) {
                    $upd->bind_param("sss", $token, $expires, $email);
                    $upd->execute();
                    $upd->close();

                    // Build reset link from current request (use HTTPS in production)
                    $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') || ($_SERVER['SERVER_PORT'] ?? '') == 443;
                    $scheme = $isHttps ? 'https' : 'http';
                    $host = $_SERVER['HTTP_HOST'] ?? 'localhost';
                    $dir = rtrim(dirname($_SERVER['REQUEST_URI'] ?? '/'), '/\\');
                    $resetLink = $scheme . '://' . $host . $dir . '/password-change.php?token=' . urlencode($token);

                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = SMTP_HOST;
                        $mail->SMTPAuth = true;
                        $mail->Username = SMTP_USERNAME;
                        $mail->Password = SMTP_PASSWORD;
                        $mail->SMTPSecure = SMTP_ENCRYPTION;
                        $mail->Port = SMTP_PORT;

                        $mail->setFrom(SMTP_USERNAME, 'Password Reset');
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = "Password Reset Request";
                        $mail->Body = "
                            <h3>Password Reset</h3>
                            <p>Click the link below to reset your password:</p>
                            <p><a href='$resetLink'>$resetLink</a></p>
                            <p>This link will expire in 1 hour.</p>
                        ";

                        $mail->send();
                        // Keep generic message even on success to avoid enumeration
                    } catch (Exception $e) {
                        // Don't reveal internal mail errors to users in production
                        error_log("Password reset email error: " . $e->getMessage());
                    }
                }
            }
            $stmt->close();
        }
    }
}

//Update Password (after clicking the token link)
if (isset($_POST['action']) && $_POST['action'] === 'update_password') {
    $token = $_POST['token'] ?? '';
    $passwordPlain = $_POST['password'] ?? '';

    // Validate token format (hex 64 chars) and password length
    if (!preg_match('/^[a-f0-9]{64}$/i', $token) || strlen($passwordPlain) < 8) {
        $message = "Invalid token or password does not meet requirements.";
    } else {
        $stmt = $conn->prepare("SELECT email FROM users WHERE reset_token = ? AND reset_expires > NOW() LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $token);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result && $result->num_rows === 1) {
                $row = $result->fetch_assoc();
                $email = $row['email'];

                $newHash = password_hash($passwordPlain, PASSWORD_DEFAULT);

                $upd = $conn->prepare("UPDATE users SET password = ?, reset_token = NULL, reset_expires = NULL WHERE email = ?");
                if ($upd) {
                    $upd->bind_param("ss", $newHash, $email);
                    $upd->execute();
                    if ($upd->affected_rows >= 1) {
                        $message = "✅ Your password has been successfully updated.";
                    } else {
                        $message = "An error occurred updating your password.";
                    }
                    $upd->close();
                } else {
                    $message = "An error occurred. Try again later.";
                }
            } else {
                $message = "❌ Invalid or expired token.";
            }
            $stmt->close();
        } else {
            $message = "An error occurred. Try again later.";
        }
    }
}

?>
<!-- ...existing code... -->
<!DOCTYPE html>
<html>
<head>
    <title>Password Reset</title>
    <style>
        body { font-family: Arial; background: #f4f4f4; padding: 40px; }
        form { background: white; padding: 20px; border-radius: 8px; max-width: 400px; margin: auto; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        input, button { width: 100%; padding: 10px; margin: 5px 0; }
        .message { background: #e7f3ff; border-left: 5px solid #2196F3; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="message"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <?php if (isset($_GET['token'])): ?>
        <!-- Reset Password Form -->
        <form method="POST">
            <h3>Enter a New Password</h3>
            <input type="hidden" name="token" value="<?= htmlspecialchars($_GET['token']) ?>">
            <input type="password" name="password" placeholder="New Password (min 8 chars)" required>
            <button type="submit" name="action" value="update_password">Update Password</button>
        </form>
    <?php else: ?>
        <!-- Send Reset Link Form -->
        <form method="POST">
            <h3>Forgot Password</h3>
            <input type="email" name="email" placeholder="Enter your email" required>
            <button type="submit" name="action" value="send_link">Send Reset Link</button>
        </form>
    <?php endif; ?>
</body>
</html>
// ...existing code...