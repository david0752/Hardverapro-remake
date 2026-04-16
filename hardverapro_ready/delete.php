<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["user_id"])) {
    die("Nem vagy bejelentkezve.");
}

$id = (int)$_GET["id"];

$stmt = $conn->prepare("DELETE FROM listings WHERE id=? AND user_id=?");
$stmt->bind_param("ii",$id,$_SESSION["user_id"]);
$stmt->execute();

header("Location: index2.php");
?>
