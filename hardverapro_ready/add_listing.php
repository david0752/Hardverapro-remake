<?php
session_start();
require_once "config.php";

if(!isset($_SESSION["user_id"])) {
    die("Jelentkezz be a hirdetés feladásához!");
}

if($_SERVER["REQUEST_METHOD"] === "POST") {
    $title       = trim($_POST["title"]);
    $desc        = trim($_POST["description"]);
    $price       = (float)$_POST["price"];
    $city        = trim($_POST["city"]);
    $category_id = (int)$_POST["category_id"];
    $user_id     = $_SESSION["user_id"];

    $stmt = $conn->prepare("
        INSERT INTO listings (user_id, title, description, price, category_id, location_city)
        VALUES (?, ?, ?, ?, ?, ?)
    ");
    $stmt->bind_param("issdis", $user_id, $title, $desc, $price, $category_id, $city);
    $stmt->execute();

    header("Location: index.php");
    exit;
}

// Kategóriák
$cat_result = $conn->query("SELECT id, name FROM categories ORDER BY name");
?>

<h2>Új hirdetés feladása</h2>
<form method="POST">
    <input name="title" placeholder="Hirdetés címe" required><br>
    <textarea name="description" placeholder="Részletes leírás" rows="6"></textarea><br>
    <input name="price" type="number" placeholder="Ár (Ft)" required><br>
    <input name="city" placeholder="Város" value="Budapest"><br>
    
    <select name="category_id" required>
        <option value="">Válassz kategóriát...</option>
        <?php while($cat = $cat_result->fetch_assoc()): ?>
            <option value="<?= $cat['id'] ?>"><?= htmlspecialchars($cat['name']) ?></option>
        <?php endwhile; ?>
    </select><br>
    
    <button>Feltöltés</button>
</form>