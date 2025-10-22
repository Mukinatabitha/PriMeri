<?php
$host = "localhost";
$user = "root";
$password = "admin123";
$dbname = "primeri";


$conn = new mysqli($host, $user, $password, $dbname,3306);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
    
}
?>