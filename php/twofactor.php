<?php
session_start();

include 'conf.php';
include 'connect.php';
require '../vendor/autoload.php';
require 'sendMail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

// Helper: safe POST read
$in = function($key){ return trim($_POST[$key] ?? ''); };

// Throttle: require at least 60s between sends per session/email
function can_send_code($email) {
    if (empty($email)) return false;
    if (!isset($_SESSION['twofa_last_sent'])) return true;
    $last = $_SESSION['twofa_last_sent'][$email] ?? 0;
    return (time() - $last) >= 60;
}
function mark_sent($email) {
    $_SESSION['twofa_last_sent'][$email] = time();
}

// Send 2FA code to email
if (isset($_POST['action']) && $_POST['action'] === 'send_code') {
    $email = $in('email');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "If an account exists for that email, a code will be sent.";
    } elseif (!can_send_code($email)) {
        $message = "Please wait before requesting another code.";
    } else {
        // Check user exists
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            // Generic message (avoid enumeration)
            $message = "If an account exists for that email, a code will be sent.";

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                $userId = $row['id'];

                // Generate numeric 6-digit code
                $code = random_int(100000, 999999);
                $codeHash = password_hash((string)$code, PASSWORD_DEFAULT);
                $expires = date("Y-m-d H:i:s", strtotime('+10 minutes'));

                // Store hashed code + expiry
                $upd = $conn->prepare("UPDATE users SET twofa_code = ?, twofa_expires = ? WHERE id = ?");
                if ($upd) {
                    $upd->bind_param("ssi", $codeHash, $expires, $userId);
                    $upd->execute();
                    $upd->close();

                    // Build email content
                    $mail = new PHPMailer(true);
                    try {
                        $mail->isSMTP();
                        $mail->Host = defined('SMTP_HOST') ? SMTP_HOST : 'smtp.example.com';
                        $mail->SMTPAuth = true;
                        $mail->Username = defined('SMTP_USERNAME') ? SMTP_USERNAME : 'you@example.com';
                        $mail->Password = defined('SMTP_PASSWORD') ? SMTP_PASSWORD : 'yourpassword';
                        $mail->SMTPSecure = defined('SMTP_ENCRYPTION') ? SMTP_ENCRYPTION : 'tls';
                        $mail->Port = defined('SMTP_PORT') ? SMTP_PORT : 587;

                        $mail->setFrom(defined('SMTP_USERNAME') ? SMTP_USERNAME : 'no-reply@example.com', 'Two-Factor Code');
                        $mail->addAddress($email);
                        $mail->isHTML(true);
                        $mail->Subject = 'Your Two-Factor Authentication Code';
                        $mail->Body = "<p>Your verification code is: <strong>{$code}</strong></p><p>It expires in 10 minutes.</p>";

                        $mail->send();
                        mark_sent($email);
                    } catch (Exception $e) {
                        error_log("TwoFA email error: " . $e->getMessage());
                        // keep generic message to user
                    }
                }
            }
            $stmt->close();
        } else {
            $message = "An error occurred. Try again later.";
        }
    }
}

// Verify code
if (isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $email = $in('email');
    $code = $in('code');

    if (!filter_var($email, FILTER_VALIDATE_EMAIL) || !preg_match('/^[0-9]{6}$/', $code)) {
        $message = "Invalid code or email.";
    } else {
        $stmt = $conn->prepare("SELECT id, twofa_code, twofa_expires FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                $hash = $row['twofa_code'];
                $expires = $row['twofa_expires'];

                if (empty($hash) || empty($expires) || strtotime($expires) < time()) {
                    $message = "Invalid or expired code.";
                } elseif (password_verify($code, $hash)) {
                    // Success: clear stored code and mark session
                    $uid = $row['id'];
                    $upd = $conn->prepare("UPDATE users SET twofa_code = NULL, twofa_expires = NULL WHERE id = ?");
                    if ($upd) {
                        $upd->bind_param("i", $uid);
                        $upd->execute();
                        $upd->close();
                    }
                    $_SESSION['twofa_verified'] = true;
                    $_SESSION['twofa_user_email'] = $email;
                    $message = "✅ Two-factor authentication successful.";
                } else {
                    $message = "Invalid code.";
                }
            } else {
                $message = "Invalid code or email.";
            }
            $stmt->close();
        } else {
            $message = "An error occurred. Try again later.";
        }
    }
}
?>
<!doctype html>
<html>
<head>
    <meta charset="utf-8">
    <title>Two-Factor Authentication</title>
    <style>
        body { font-family: Arial, sans-serif; background:#f6f6f6; padding:30px; }
        form { background:#fff; padding:20px; border-radius:6px; max-width:420px; margin:20px auto; box-shadow:0 2px 8px rgba(0,0,0,0.06);}
        input, button { width:100%; padding:10px; margin:8px 0; box-sizing:border-box;}
        .msg { background:#eef7ee; border-left:4px solid #4caf50; padding:10px; margin-bottom:10px;}
    </style>
</head>
<body>
    <?php if (!empty($message)): ?>
        <div class="msg"><?= htmlspecialchars($message) ?></div>
    <?php endif; ?>

    <!-- Request code -->
    <form method="POST">
        <h3>Send 2FA Code</h3>
        <input type="email" name="email" placeholder="Your email" required>
        <button type="submit" name="action" value="send_code">Send Code</button>
        <small>Code valid for 10 minutes. Wait 60s between requests.</small>
    </form>

    <!-- Verify code -->
    <form method="POST">
        <h3>Verify Code</h3>
        <input type="email" name="email" placeholder="Your email" required>
        <input type="text" name="code" placeholder="6-digit code" pattern="\d{6}" required>
        <button type="submit" name="action" value="verify_code">Verify</button>
    </form>
</body>
</html>
// ...existing code...