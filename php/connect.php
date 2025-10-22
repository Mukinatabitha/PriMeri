<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "primeri";


$conn = new mysqli($host, $user, $password, $dbname,3308);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
else {
    
}
?>
