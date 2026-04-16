<?php
session_start();
require_once "config.php";

$search = isset($_GET["q"]) ? trim($_GET["q"]) : "";
$category = isset($_GET["category"]) ? (int)$_GET["category"] : 0;

// Kategóriák lekérdezése a szűrőkhöz
$cat_stmt = $conn->prepare("SELECT id, name FROM categories ORDER BY name");
$cat_stmt->execute();
$cat_result = $cat_stmt->get_result();

// Fő lekérdezés
$sql = "
    SELECT listings.*, users.username, categories.name as category_name 
    FROM listings 
    JOIN users ON listings.user_id = users.id 
    LEFT JOIN categories ON listings.category_id = categories.id 
    WHERE listings.status = 'active'
";

$params = [];
$types = "";

if ($search !== "") {
    $sql .= " AND listings.title LIKE ?";
    $types .= "s";
    $params[] = "%" . $search . "%";
}

if ($category > 0) {
    $sql .= " AND listings.category_id = ?";
    $types .= "i";
    $params[] = $category;
}

$sql .= " ORDER BY listings.created_at DESC";

$stmt = $conn->prepare($sql);

if (!empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <title>HardverApró</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="Fontawesome/css/brands.min.css">
    <link rel="stylesheet" href="Fontawesome/css/solid.min.css">
</head>
<body>

<div class="container">

    <h1>HardverApró</h1>
    <p class="slogan">Magyarország legnagyobb hardver piactere</p>

    <?php if(isset($_SESSION["user_id"])): ?>
        <p>Bejelentkezve mint <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong> | 
        <a href="logout.php">Kijelentkezés</a> | 
        <a href="add_listing.php">+ Hirdetés feladása</a></p>
    <?php else: ?>
        <p><a href="login.php">Bejelentkezés</a> | <a href="register.php">Regisztráció</a></p>
    <?php endif; ?>

    <hr>

    <form method="GET">
        <input type="text" name="q" placeholder="Mit keresel?" value="<?= htmlspecialchars($search) ?>">
        <button>Keresés</button>
    </form>

    <!-- Kategória szűrők -->
    <div class="category-filters">
        <a href="index.php<?= $search ? '?q='.urlencode($search) : '' ?>" 
           class="category-btn <?= $category == 0 ? 'active' : '' ?>">Összes</a>
        
        <?php while($cat = $cat_result->fetch_assoc()): ?>
            <?php 
            $url = "index.php?category=" . $cat['id'];
            if ($search) $url .= "&q=" . urlencode($search);
            ?>
            <a href="<?= htmlspecialchars($url) ?>" 
               class="category-btn <?= $category == $cat['id'] ? 'active' : '' ?>">
                <?= htmlspecialchars($cat['name']) ?>
            </a>
        <?php endwhile; ?>
    </div>

    <?php if($result->num_rows == 0): ?>
        <p>Nincs találat.</p>
    <?php else: ?>
        <?php while($row = $result->fetch_assoc()): ?>
<a href="termek.php?id=<?= $row['id'] ?>" class="card">

    <div class="card-content">
        <div class="title">
            <?= htmlspecialchars($row['title']) ?>
        </div>

        <div class="price">
            <?= number_format($row['price'], 0, " ", " ") ?> Ft
        </div>

        <div class="meta">
            <?= htmlspecialchars($row['category_name'] ?? 'Nincs') ?> • 
            <?= htmlspecialchars($row['username']) ?>
        </div>
    </div>

    <div class="card-media">

        <?php
            $images = json_decode($row['images'] ?? '["asd.jpg", "asd2.jpg"]', true);
        ?>

        <div class="carousel" data-images='<?= htmlspecialchars(json_encode($images)) ?>'>
            <button class="nav prev" onclick="event.preventDefault()">‹</button>
            <img src="<?= htmlspecialchars($images[0]) ?>" class="carousel-img">
            <button class="nav next" onclick="event.preventDefault()">›</button>
        </div>

    </div>

</a>
        <?php endwhile; ?>
    <?php endif; ?>

</div>
<script>
document.querySelectorAll(".carousel").forEach(carousel => {

    const images = JSON.parse(carousel.dataset.images || '[]');
    const img = carousel.querySelector(".carousel-img");

    let index = 0;

    const prev = carousel.querySelector(".prev");
    const next = carousel.querySelector(".next");

    function update() {
        if (images.length > 0) {
            img.src = images[index];
        }
    }

    next.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        index = (index + 1) % images.length;
        update();
    });

    prev.addEventListener("click", (e) => {
        e.preventDefault();
        e.stopPropagation();
        index = (index - 1 + images.length) % images.length;
        update();
    });

});
</script>

</body>
</html>