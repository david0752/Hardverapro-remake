<?php
session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] === "POST") {

    $username = $_POST["username"];
    $email = $_POST["email"];
    $password = password_hash($_POST["password"], PASSWORD_DEFAULT);

    $stmt = $conn->prepare("INSERT INTO users (username,email,password_hash) VALUES (?,?,?)");
    $stmt->bind_param("sss",$username,$email,$password);

    if($stmt->execute()){
        header("Location: login.php");
    } else {
        echo "Hiba történt.";
    }
}
?>

<h2>Regisztráció</h2>
<form method="POST">
    <input name="username" placeholder="Felhasználónév" required><br>
    <input name="email" type="email" placeholder="Email" required><br>
    <input name="password" type="password" placeholder="Jelszó" required><br>
    <button>Regisztráció</button>
</form>
