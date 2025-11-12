<?php
session_start();


// Only handle POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header("Location: ../html/twofactor.html");
    exit();
}

// Retrieve and sanitize user input
$user_code = trim($_POST['code'] ?? '');

// Validate input format (must be exactly 6 digits)
if (!preg_match('/^\d{6}$/', $user_code)) {
    header("Location: ../html/twofactor.html?error=Invalid+code+format");
    exit();
}

// Ensure a code and expiry time exist in session
if (empty($_SESSION['2fa_code']) || empty($_SESSION['2fa_code_expiry'])) {
    header("Location: ../html/twofactor.html?error=No+code+generated");
    exit();
}

// Check expiration (10 minutes default)
if (time() > $_SESSION['2fa_code_expiry']) {
    unset($_SESSION['2fa_code'], $_SESSION['2fa_code_expiry']);
    header("Location: ../html/twofactor.html?error=Code+expired");
    exit();
}

// Compare codes securely
if (hash_equals($_SESSION['2fa_code'], $user_code)) {
    // Clear session values after successful verification
    unset($_SESSION['2fa_code'], $_SESSION['2fa_code_expiry']);

    // Redirect to protected page or dashboard
    header("Location: ../html/dashboard.html");
    exit();
} else {
    header("Location: ../html/twofactor.html?error=Incorrect+code");
    exit();
}
?>
