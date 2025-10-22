<?php
session_start();
include 'connect.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = trim($_POST['email']);
    $password = $_POST['password'];

    // Validate input
    if (empty($email) || empty($password)) {
        $_SESSION['error'] = "Please fill in all fields.";
        header("Location: ../html/login.html");
        exit();
    }

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $_SESSION['error'] = "Invalid email format.";
        header("Location: ../html/login.html");
        exit();
    }

    // Check if user exists and verify password
    $stmt = $conn->prepare("SELECT id, name, email, password, accountType FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($result->num_rows === 1) {
        $user = $result->fetch_assoc();

        // Verify password
        if (password_verify($password, $user['password'])) {
            // Login successful - set session variables
            $_SESSION['user_id'] = $user['id'];
            $_SESSION['user_name'] = $user['name'];
            $_SESSION['user_email'] = $user['email'];
            $_SESSION['account_type'] = $user['accountType'];
            $_SESSION['logged_in'] = true;

            // Redirect based on account type
            if ($user['accountType'] === 'manufacturer') {
                header("Location: ../html/manufacturer.html");
                exit();
            } elseif ($user['accountType'] === 'buyer') {
                header("Location: ../html/catalog.html");
                exit();
            }

        } else {
            // Invalid password
            $_SESSION['error'] = "Invalid email or password.";
            header("Location: ../html/login.html");
            exit();
        }
    } else {
        // User not found
        $_SESSION['error'] = "Invalid email or password.";
        header("Location: ../html/login.html");
        exit();
    }

    $stmt->close();
}

$conn->close();
?>
