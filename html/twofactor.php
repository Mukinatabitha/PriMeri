<?php
session_start();

include '../php/conf.php';
include '../php/connect.php';
require '../vendor/autoload.php';
require '../php/sendMail.php';

use PHPMailer\PHPMailer\PHPMailer;
use PHPMailer\PHPMailer\Exception;

$message = "";

// Helper: safe POST read
$in = function($key){ return trim($_POST[$key] ?? ''); };

// Throttle: require at least 10s between sends per session/email
function can_send_code($email) {
    if (empty($email)) return false;
    if (!isset($_SESSION['twofa_last_sent'])) return true;
    $last = $_SESSION['twofa_last_sent'][$email] ?? 0;
    return (time() - $last) >= 10;
}
function mark_sent($email) {
    $_SESSION['twofa_last_sent'][$email] = time();
}

// Get email from session or redirect to login
if (!isset($_SESSION['user_email']) && !isset($_SESSION['twofa_user_email'])) {
    header("Location: ../html/login.html");
    exit();
}

$userEmail = $_SESSION['user_email'] ?? $_SESSION['twofa_user_email'] ?? '';

// Auto-send 2FA code on page load if not already sent or expired
if (empty($_SESSION['twofa_code_sent']) || !can_send_code($userEmail)) {
    sendTwoFACode($userEmail);
}

// Send 2FA code to email
function sendTwoFACode($email) {
    global $db, $message;
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = "Invalid email address.";
        return false;
    } elseif (!can_send_code($email)) {
        $message = "Please wait before requesting another code.";
        return false;
    }

    // Check user exists
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ? LIMIT 1");
    if ($stmt) {
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $res = $stmt->get_result();

        if ($res && $res->num_rows === 1) {
            $row = $res->fetch_assoc();
            $userId = $row['id'];

            // Generate numeric 6-digit code
            $code = random_int(100000, 999999);
            $codeHash = password_hash((string)$code, PASSWORD_DEFAULT);
            $expires = date("Y-m-d H:i:s", strtotime('+10 minutes'));

            // Store hashed code + expiry
            $upd = $db->prepare("UPDATE users SET twofa_code = ?, twofa_expires = ? WHERE id = ?");
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

                    if ($mail->send()) {
                        mark_sent($email);
                        $_SESSION['twofa_code_sent'] = true;
                        $message = "✅ Verification code has been sent to your email.";
                        return true;
                    }
                } catch (Exception $e) {
                    error_log("TwoFA email error: " . $e->getMessage());
                    $message = "Failed to send email. Please try again.";
                }
            }
        } else {
            $message = "Account not found. Please try logging in again.";
        }
        $stmt->close();
    } else {
        $message = "An error occurred. Try again later.";
    }
    return false;
}

// Resend code functionality
if (isset($_POST['action']) && $_POST['action'] === 'resend_code') {
    if (sendTwoFACode($userEmail)) {
        $message = "✅ New verification code has been sent to your email.";
    }
}

// Verify code
if (isset($_POST['action']) && $_POST['action'] === 'verify_code') {
    $code = $in('code');

    if (!preg_match('/^[0-9]{6}$/', $code)) {
        $message = "Invalid code format. Please enter a 6-digit number.";
    } else {
        $stmt = $db->prepare("SELECT id, twofa_code, twofa_expires FROM users WHERE email = ? LIMIT 1");
        if ($stmt) {
            $stmt->bind_param("s", $userEmail);
            $stmt->execute();
            $res = $stmt->get_result();

            if ($res && $res->num_rows === 1) {
                $row = $res->fetch_assoc();
                $hash = $row['twofa_code'];
                $expires = $row['twofa_expires'];

                if (empty($hash) || empty($expires) || strtotime($expires) < time()) {
                    $message = "Invalid or expired code. Please request a new one.";
                } elseif (password_verify($code, $hash)) {
                    // Success: clear stored code and mark session
                    $uid = $row['id'];
                    $upd = $db->prepare("UPDATE users SET twofa_code = NULL, twofa_expires = NULL WHERE id = ?");
                    if ($upd) {
                        $upd->bind_param("i", $uid);
                        $upd->execute();
                        $upd->close();
                    }
                    $_SESSION['twofa_verified'] = true;
                    $_SESSION['twofa_user_email'] = $userEmail;
                    unset($_SESSION['twofa_code_sent']);
                    
                    $message = "✅ Two-factor authentication successful.";
                    
                    // Determine redirect based on account type
                    $typeStmt = $db->prepare("SELECT accountType FROM users WHERE email = ? LIMIT 1");
                    if ($typeStmt) {
                        $typeStmt->bind_param("s", $userEmail);
                        $typeStmt->execute();
                        $typeRes = $typeStmt->get_result();
                        if ($typeRes && $typeRes->num_rows === 1) {
                            $userRow = $typeRes->fetch_assoc();
                            $accountType = $userRow['accountType'];
                            
                            if ($accountType === 'buyer') {
                                header("Location: ../html/home.html");
                                exit();
                            } else {
                                header("Location: ../html/my_store.html");
                                exit();
                            }
                        }
                        $typeStmt->close();
                    }
                    
                    // Fallback redirect
                    header("Location: ../html/home.html");
                    exit();
                } else {
                    $message = "Invalid code. Please try again.";
                }
            } else {
                $message = "Account not found. Please try logging in again.";
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
        .container { background:#fff; padding:30px; border-radius:8px; max-width:420px; margin:40px auto; box-shadow:0 4px 12px rgba(0,0,0,0.1); text-align: center;}
        input, button { width:100%; padding:12px; margin:12px 0; box-sizing:border-box; border-radius:4px;}
        input { border:1px solid #ddd; font-size:16px; text-align:center; letter-spacing:8px;}
        button { background:#4caf50; color:white; border:none; cursor:pointer; font-size:16px; font-weight:bold;}
        button:hover { background:#45a049;}
        button.secondary { background:#6c757d;}
        button.secondary:hover { background:#5a6268;}
        .msg { background:#eef7ee; border-left:4px solid #4caf50; padding:12px; margin-bottom:20px; border-radius:4px;}
        .error { background:#fde8e8; border-left:4px solid #e53e3e; color:#c53030;}
        h3 { color:#333; margin-bottom:20px;}
        .info { color:#666; font-size:14px; margin:15px 0;}
        .resend { margin-top:20px; padding-top:20px; border-top:1px solid #eee;}
    </style>
</head>
<body>
    <div class="container">
        <h3>Two-Factor Authentication</h3>
        
        <?php if (!empty($message)): ?>
            <div class="msg <?php echo strpos($message, 'error') !== false ? 'error' : ''; ?>">
                <?= htmlspecialchars($message) ?>
            </div>
        <?php endif; ?>

        <div class="info">
            <p>We've sent a 6-digit verification code to:</p>
            <p><strong><?= htmlspecialchars($userEmail) ?></strong></p>
            <p>Please check your email and enter the code below.</p>
        </div>

        <!-- Verify code form -->
        <form method="POST">
            <input type="text" name="code" placeholder="Enter 6-digit code" pattern="\d{6}" maxlength="6" required autofocus>
            <button type="submit" name="action" value="verify_code">Verify Code</button>
        </form>

        <div class="resend">
            <p>Didn't receive the code?</p>
            <form method="POST" style="margin:0;">
                <button type="submit" name="action" value="resend_code" class="secondary">Resend Code</button>
            </form>
            <small>Wait 60 seconds between requests</small>
        </div>
    </div>
</body>
</html>
