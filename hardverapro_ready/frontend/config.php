<?php
$host = "localhost";
$db   = "hardverapro";
$user = "root";
$pass = "";

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Hiba: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
