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

    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        die("Error: Invalid email format.");
    }
    else if($emailExists = $conn->prepare("SELECT id FROM users WHERE email = ?"))
    {
        $emailExists->bind_param("s", $email);
        $emailExists->execute();
        $emailExists->store_result();
        if($emailExists->num_rows > 0) {
            die("Error: Email already registered.");
        }
        $emailExists->close();
    }
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
