<?php
include 'connect.php';
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullName = $_POST['fullName'];
    $email = $_POST['email'];
    $accountType = $_POST['accountType'];
    $password = $_POST['password'];

    $stmt = $conn->prepare("INSERT INTO users (full_name, email, accountType, password) VALUES (?, ?, ?, ?)");
    $stmt->bind_param("ssss", $fullName, $email, $accountType, $password);
    if ($stmt->execute()) {
        include 'sendMail.php';
        registrationEmail($email, $fullName);
        header("Location: ../html/login.html");
    } else {
        echo "Error: " . $stmt->error;
    }
    $stmt->close();
}
?>