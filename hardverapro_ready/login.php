<?php
session_start();
require_once "config.php";

if($_SERVER["REQUEST_METHOD"] === "POST") {

    $email = $_POST["email"];
    $password = $_POST["password"];

    $stmt = $conn->prepare("SELECT * FROM users WHERE email=?");
    $stmt->bind_param("s",$email);
    $stmt->execute();
    $result = $stmt->get_result();
    $user = $result->fetch_assoc();

    if($user && password_verify($password,$user["password_hash"])) {
        $_SESSION["user_id"] = $user["id"];
        $_SESSION["username"] = $user["username"];
        header("Location: index.php");
        exit;
    } else {
        echo "Hibás adatok.";
    }
}
?>

<h2>Bejelentkezés</h2>
<form method="POST">
    <input name="email" placeholder="Email"><br>
    <input name="password" type="password" placeholder="Jelszó"><br>
    <button>Bejelentkezés</button>
</form>
