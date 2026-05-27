<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["user_id"])) {
    die("Nem vagy bejelentkezve.");
}

$id = (int)$_GET["id"];

$stmt = $conn->prepare("SELECT * FROM listings WHERE id=? AND user_id=?");
$stmt->bind_param("ii", $id, $_SESSION["user_id"]);
$stmt->execute();
$listing = $stmt->get_result()->fetch_assoc();

if(!$listing) {
    die("Nincs jogosultságod ehhez a hirdetéshez.");
}

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $desc        = trim($_POST["description"]);
    $price       = (float)$_POST["price"];
    $city        = trim($_POST["city"]);
    $category_id = (int)$_POST["category_id"];

    $stmt = $conn->prepare("
        UPDATE listings 
        SET title=?, description=?, price=?, location_city=?, category_id=?
        WHERE id=? AND user_id=?
    ");
    $stmt->bind_param("ssdsiii", $title, $desc, $price, $city, $category_id, $id, $_SESSION["user_id"]);
    $stmt->execute();

    header("Location: termek.php?id=".$id);
    exit;
}

$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
?>

<h2>Hirdetés szerkesztése</h2>
<form method="POST">
    <input name="title" value="<?= htmlspecialchars($listing["title"]) ?>" required><br>
    <textarea name="description"><?= htmlspecialchars($listing["description"]) ?></textarea><br>
    <input name="price" type="number" value="<?= $listing["price"] ?>" required><br>
    <input name="city" value="<?= htmlspecialchars($listing["location_city"]) ?>"><br>
    
    <select name="category_id" required>
        <?php while($cat = $cat_result->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>" <?= $listing["category_id"] == $cat['id'] ? 'selected' : '' ?>>
                <?= htmlspecialchars($cat['name']) ?>
            </option>
        <?php endwhile; ?>
    </select><br>
    
    <button>Mentés</button>
</form>