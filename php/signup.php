<?php
include 'connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $accountType = $_POST['accountType'];
    $password = $_POST['password'];
    $confirmPassword = $_POST['confirmPassword'];

    if ($password !== $confirmPassword) {
        die("Error: Passwords do not match.");
    }

    // Hash password before storing
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (name, email, accountType, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $email, $accountType, $hashedPassword);

    if ($stmt->execute()) {
        include 'sendMail.php';
        registrationEmail($email, $fullName);
        header("Location: ../html/login.html");
        exit();
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>
