<?php
session_start();
require_once "config.php";

if(!isset($_GET['id'])) die("Nincs termék megadva.");

$id = (int)$_GET['id'];

$sql = "SELECT listings.*, users.username, categories.name as category_name 
        FROM listings 
        JOIN users ON listings.user_id = users.id 
        LEFT JOIN categories ON listings.category_id = categories.id
        WHERE listings.id = ?";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$row = $stmt->get_result()->fetch_assoc();

if(!$row) die("Nem található hirdetés.");
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title><?= htmlspecialchars($row['title']) ?></title>
    <link rel="stylesheet" href="Style.css">
</head>
<body>

<div class="container">
    <h1><?= htmlspecialchars($row['title']) ?></h1>
    <p><?= nl2br(htmlspecialchars($row['description'])) ?></p>

    <div class="price"><?= number_format($row['price'],0," "," ") ?> Ft</div>
    <p class="small">Eladó: <?= htmlspecialchars($row['username']) ?></p>
    <p class="small">Város: <?= htmlspecialchars($row['location_city']) ?></p>
    <p class="small">Kategória: <?= htmlspecialchars($row['category_name'] ?? 'Nincs megadva') ?></p>

    <?php if(isset($_SESSION["user_id"]) && $_SESSION["user_id"] == $row["user_id"]): ?>
        <div class="actions">
            <a href="edit.php?id=<?= $row["id"] ?>">Szerkesztés</a> |
            <a href="delete.php?id=<?= $row["id"] ?>" onclick="return confirm('Biztosan törlöd?')">Törlés</a>
        </div>
    <?php endif; ?>

    <hr>
    <a href="index.php">← Vissza a főoldalra</a>
</div>

</body>
</html>