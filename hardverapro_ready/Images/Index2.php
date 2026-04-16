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
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="asd.css">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="Fontawesome/css/brands.min.css">
    <link rel="stylesheet" href="Fontawesome/css/solid.min.css">
    <title>Hardverapro</title>
</head>

<body>
    <header>
        <nav>
            <!-- HARDVERAPRO logo gomb -->
            <div class="navButtonLogo">
                <div class="navBtnBackground"></div>
                <p class="navBtnText"><img src="./Images/logo.png" alt="HARDVERAPRO"></p>
            </div>
            <!-- További navigáció -->
            <div class="navButton">
                <div class="navBtnBackground"></div>
                <p class="navBtnText">PROHARDVER!</p>
            </div>
            <div class="navButton">
                <div class="navBtnBackground"></div>
                <p class="navBtnText">Mobilarena</p>
            </div>
            <div class="navButton">
                <div class="navBtnBackground"></div>
                <p class="navBtnText">LOGOUT</p>
            </div>
        </nav>
    </header>

    <main>
        <!-- Kereső sáv -->
        <div class="search-bar">
            <input type="text" placeholder="Itt megtalálod, amit keresel!" id="searchInput">
            <button class="search-btn">Keresés</button>
        </div>

        <?php
// 1. Design tömb (marad a fix sorrend)
$design = [
    ['name' => 'Hardver',       'icon' => 'fa-microchip'],
    ['name' => 'Notebook',      'icon' => 'fa-laptop'],
    ['name' => 'PC, Szerver',   'icon' => 'fa-desktop'],
    ['name' => 'Mobil, Tablet', 'icon' => 'fa-mobile-screen'],
    ['name' => 'Konzol',        'icon' => 'fa-gamepad'],
    ['name' => 'TV-Audió',      'icon' => 'fa-tv'],
    ['name' => 'Fotó, videó',   'icon' => 'fa-camera'],
    ['name' => 'Egyéb',         'icon' => 'fa-ellipsis']
];

// 2. Adatbázis kategóriák – tisztított névvel
$db_cats_by_name = [];
while ($row = $cat_result->fetch_assoc()) {
    // Minden nevet kisbetűssé teszünk és levágjuk a szóközöket a széléről
    $clean_db_name = trim(mb_strtolower($row['name']));
    $db_cats_by_name[$clean_db_name] = $row['id'];
}
?>

<div class="categories">
    <?php
    foreach ($design as $item):
        // Itt is tisztítjuk a nevet az összehasonlításhoz
        $search_name = trim(mb_strtolower($item['name']));
        
        // Ha nem találja, próbáljuk meg a vessző/ékezet eltérések miatt manuálisan is csekkolni
        $cat_id = isset($db_cats_by_name[$search_name]) ? $db_cats_by_name[$search_name] : 0;

        // SPECIÁLIS JAVÍTÁS: Ha a "Fotó, videó" még mindig nem jó az ékezet miatt
        if ($cat_id == 0 && str_contains($search_name, 'fotó')) {
             // Megkeressük azt a kulcsot, amiben benne van a "fotó" szó
             foreach($db_cats_by_name as $db_name => $id) {
                 if(str_contains($db_name, 'fot')) { $cat_id = $id; break; }
             }
        }

        $url = ($cat_id > 0) ? "index2.php?category=" . $cat_id : "#";
    ?>
        <a href="<?= htmlspecialchars($url) ?>"
            class="category-item <?= ($cat_id > 0 && isset($category) && $category == $cat_id) ? 'active' : '' ?>">
            <div class="icon-box">
                <i class="fa-solid <?= $item['icon'] ?>"></i>
            </div>
            <span><?= htmlspecialchars($item['name']) ?></span>
            <?php if($cat_id == 0): ?> <!-- Csak teszteléshez: ha 0, kiírja a hibát -->
                <small style="color:red; font-size:10px;">Nem található</small>
            <?php endif; ?>
        </a>
    <?php endforeach; ?>
</div>









        <!-- Sárga filter/pagination sáv -->
        <div class="filter-bar">
            <div class="pagination-controls">
                <button class="arrow-btn"><i class="fa-solid fa-chevron-left"></i></button>
                <span class="page-info">1-100 db / frissítettek</span>
                <button class="arrow-btn"><i class="fa-solid fa-chevron-right"></i></button>
            </div>
            <div class="view-options">
                <button class="view-btn active"><i class="fa-solid fa-table-cells"></i></button>
                <button class="view-btn"><i class="fa-solid fa-list"></i></button>
            </div>
        </div>

        <div class="content-wrapper">
            <!-- Bal oldal: hirdetések -->
            <div class="ads-section">
                <!-- Kiemeltek -->
                <div class="section-header kiemelt">
                    <span>Kiemeltek</span>
                </div>
                <div class="ads-list">
                    <!-- 4 db kiemelt hirdetés (pontosan a kép szerint) -->
                    <?php if ($result->num_rows == 0): ?>
                        <p>Nincs találat.</p>
                    <?php else: ?>
                        <?php while ($row = $result->fetch_assoc()): ?>
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

                <!-- Normál hirdetések -->
                <div class="section-header normal">
                    <span>Normál hirdetések</span>
                </div>
                <div class="ads-list">
                    <!-- 5 db normál hirdetés -->
                    <div class="ad-item">
                        <div class="ad-image-container">
                            <img src="./Images/proba-img.jpg" alt="Hirdetés kép">
                            <div class="bazar-badge">BAZAR</div>
                        </div>
                        <div class="ad-main">
                            <div class="ad-title">Minden szoftver mellé teljesen audit és NIS2 biztos, jogilag hiteles licenszgazolást adunk át!</div>
                            <div class="ad-price">14 990 Ft</div>
                        </div>
                        <div class="seller-info">
                            <img src="./Images/profie-icon.gif" alt="Profil" class="seller-icon">
                            <div>
                                <div class="seller-name">DextyPremium <i class="fa-solid fa-circle-check verified"></i></div>
                                <div class="seller-location">Budapest</div>
                                <div class="eloresorolt">Előresorolt</div>
                            </div>
                        </div>
                    </div>
                    <!-- Ismétlődik 4x ugyanúgy (a kép szerint) -->
                    <div class="ad-item"> <!-- ugyanaz a tartalom mint fent -->
                        <div class="ad-image-container">
                            <img src="./Images/proba-img.jpg" alt="Hirdetés kép">
                            <div class="bazar-badge">BAZAR</div>
                        </div>
                        <div class="ad-main">
                            <div class="ad-title">Minden szoftver mellé teljesen audit és NIS2 biztos, jogilag hiteles licenszgazolást adunk át!</div>
                            <div class="ad-price">14 990 Ft</div>
                        </div>
                        <div class="seller-info">
                            <img src="./Images/profie-icon.gif" alt="Profil" class="seller-icon">
                            <div>
                                <div class="seller-name">DextyPremium <i class="fa-solid fa-circle-check verified"></i></div>
                                <div class="seller-location">Budapest</div>
                                <div class="eloresorolt">Előresorolt</div>
                            </div>
                        </div>
                    </div>
                    <div class="ad-item"> <!-- ugyanaz --> </div>
                    <div class="ad-item"> <!-- ugyanaz --> </div>
                    <div class="ad-item"> <!-- ugyanaz --> </div>
                </div>
            </div>

            <!-- Jobb oldal: Fontos tudnod + Állásajánlatok -->
            <div class="sidebar">
                <!-- Fontos tudnod -->
                <div class="fontos-tudnod">
                    <div class="fontos-tudnod-header">
                        <i class="fa-solid fa-triangle-exclamation"></i>
                        <span>Fontos tudnod!</span>
                    </div>
                    <div class="fontos-tudnod-main">
                        <p><strong>Hogyan működik a Hardverapro?</strong></p>
                        <p>Soha ne utalj előre! Probléma, csalás esetén bármely hirdetésben ott a "Jelentem" gomb, használd!</p>
                        <p><strong>Mit tilos hirdetni?</strong></p>
                        <p><a href="#">További információk</a></p>
                    </div>
                </div>

                <!-- Állásajánlatok -->
                <div class="allasajanlatok">
                    <div class="allasajanlatok-header">
                        <span>Állásajánlatok</span>
                    </div>
                    <div class="allas-list">
                        <!-- 4 db állás (a kép szerint) -->
                        <div class="allas-item">
                            <div class="allas-top">
                                <img src="./Images/proba-img.jpg" alt="Állás kép">
                                <span>Számítástechnikai értékesítő</span>
                            </div>
                            <div class="allas-bottom">
                                <p><strong>Cég:</strong> Laptopműhely Bt.</p>
                                <p><strong>Város:</strong> Budapest</p>
                                <button class="reszletek-btn">Részletek</button>
                            </div>
                        </div>
                        <!-- Ismétlődik 3x -->
                        <div class="allas-item"> <!-- ugyanaz --> </div>
                        <div class="allas-item"> <!-- ugyanaz --> </div>
                        <div class="allas-item"> <!-- ugyanaz --> </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer></footer>
    <script src="Script.js"></script>
</body>

</html>