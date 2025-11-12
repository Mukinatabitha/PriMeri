<?php
$host = "localhost";
$user = "root";
$password = "";
$dbname = "primeri";


$db = new mysqli($host, $user, $password, $dbname,3308);

if ($db->connect_error) {
    die("Connection failed: " . $db->connect_error);
}
else {
    
}
?>
