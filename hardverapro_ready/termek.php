<?php
session_start();
require_once "config.php";

if (!isset($_GET['id']) || !is_numeric($_GET['id'])) {
    die("Hibás termékazonosító.");
}

$id = (int)$_GET['id'];

// Fő hirdetés
$sql = "
    SELECT listings.*, 
           users.username, 
           users.id as user_id,
           categories.name as category_name 
    FROM listings 
    JOIN users ON listings.user_id = users.id 
    LEFT JOIN categories ON listings.category_id = categories.id 
    WHERE listings.id = ? 
      AND listings.status = 'active'
";

$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $id);
$stmt->execute();
$result = $stmt->get_result();
$row = $result->fetch_assoc();

if (!$row) {
    die("A keresett hirdetés nem található vagy nem aktív.");
}

// Képek
$images = [];
if (isset($row['images']) && !empty($row['images'])) {
    $images = json_decode($row['images'], true);
}
if (empty($images)) {
    $images = ["./Images/logo.png"];
}

// További hirdetések
$other_listings = [];
$other_sql = "SELECT id, title, price FROM listings WHERE user_id = ? AND id != ? AND status = 'active' ORDER BY created_at DESC LIMIT 6";
$other_stmt = $conn->prepare($other_sql);
$other_stmt->bind_param("ii", $row['user_id'], $id);
$other_stmt->execute();
$other_result = $other_stmt->get_result();
while ($ol = $other_result->fetch_assoc()) {
    $other_listings[] = $ol;
}

// Hozzászólások
$comments = [];
$comm_sql = "
    SELECT c.*, u.username 
    FROM comments c 
    JOIN users u ON c.user_id = u.id 
    WHERE c.listing_id = ? 
    ORDER BY c.created_at DESC
";
$comm_stmt = $conn->prepare($comm_sql);
$comm_stmt->bind_param("i", $id);
$comm_stmt->execute();
$comments_result = $comm_stmt->get_result();
while ($c = $comments_result->fetch_assoc()) {
    $comments[] = $c;
}
?>

<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="Fontawesome/css/fontawesome.min.css">
    <link rel="stylesheet" href="Fontawesome/css/brands.min.css">
    <link rel="stylesheet" href="Fontawesome/css/solid.min.css">
    <title><?= htmlspecialchars($row['title']) ?> - Hardverapro</title>
    
    <style>
        .product-page { background: #f8f9fa; }
        .product-content { max-width: 1280px; margin: 25px auto; padding: 0 20px; }

        /* === NAVBAR === */
        header nav {
            background: #1f2937;
            padding: 0 30px;
            height: 70px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            box-shadow: 0 2px 10px rgba(0,0,0,0.2);
            position: sticky;
            top: 0;
            z-index: 1000;
        }
        .nav-left { display: flex; align-items: center; gap: 15px; }
        .navButtonLogo { cursor: pointer; }
        .navButton {
            padding: 8px 18px;
            color: white;
            font-weight: 600;
            border-radius: 8px;
            transition: 0.2s;
        }
        .navButton:hover { background: rgba(251,191,36,0.25); }

        .nav-right {
            display: flex;
            align-items: center;
            gap: 20px;
            color: white;
        }
        .nav-right a { 
            color: #fbbf24; 
            font-weight: 600; 
            text-decoration: none;
            transition: color 0.2s;
        }
        .nav-right a:hover { color: white; }

        .user-icon {
            width: 38px;
            height: 38px;
            background: #fbbf24;
            color: #1f2937;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 18px;
            font-weight: bold;
        }

        /* További stílusok */
        .product-header { background: white; padding: 20px 25px; border-radius: 12px; box-shadow: 0 4px 15px rgba(0,0,0,0.08); margin-bottom: 25px; display: flex; align-items: center; gap: 15px; }
        .back-button { display: inline-flex; align-items: center; gap: 8px; color: #374151; font-weight: 600; text-decoration: none; padding: 8px 16px; background: #f1f5f9; border-radius: 8px; }
        .back-button:hover { background: #fbbf24; color: #1f2937; }

        .product-main { display: grid; grid-template-columns: 1fr 360px; gap: 30px; }
        .product-left { background: white; border-radius: 12px; border: 1px solid rgba(251,191,36,0.2); overflow: hidden; box-shadow: 0 6px 20px rgba(0,0,0,0.1); }
        .info-bar { padding: 14px 22px; background: #f8fafc; border-bottom: 1px solid #e5e7eb; display: flex; flex-wrap: wrap; gap: 22px; font-size: 14px; }
        .info-bar i { color: #fbbf24; }

        .product-image-box { padding: 25px 30px; min-height: 520px; display: flex; align-items: center; justify-content: center; border-bottom: 1px solid #f1f5f9; }
        .product-carousel { width: 100% !important; height: 480px !important; border: 1px solid #e5e7eb; border-radius: 12px; position: relative; overflow: hidden; }
        .product-carousel img { width: 100%; height: 100%; object-fit: contain; }
        .product-carousel .nav { position: absolute; top: 50%; transform: translateY(-50%); width: 52px; height: 52px; background: rgba(0,0,0,0.5); color: white; border: none; border-radius: 50%; font-size: 28px; cursor: pointer; }
        .product-carousel .nav:hover { background: #fbbf24; color: #1f2937; }

        .description-box { padding: 30px; }
        .price-large { font-size: 38px; font-weight: 800; color: #e01212; margin: 10px 0 15px; }

        .sidebar-box { background: white; border: 1px solid #e5e7eb; padding: 20px; margin-bottom: 20px; border-radius: 12px; }
        .action-buttons { display: flex; gap: 12px; margin-top: 15px; }
        .btn-jelentem { flex: 1; padding: 14px; background: #ef4444; color: white; border: none; border-radius: 10px; font-weight: 600; }
        .btn-ertekel { flex: 1; padding: 14px; background: #10b981; color: white; border: none; border-radius: 10px; font-weight: 600; }

        .other-listings-box, .comments-section { padding: 30px; border-top: 1px solid #e5e7eb; }

        .comment-form {
            margin-top: 25px;
            background: #f8fafc;
            padding: 20px;
            border-radius: 12px;
            border: 1px solid #e5e7eb;
        }
        .comment-form textarea {
            width: 100%;
            height: 100px;
            padding: 12px;
            border: 1px solid #cbd5e1;
            border-radius: 8px;
            resize: vertical;
            font-family: inherit;
        }
        .comment-form button {
            margin-top: 12px;
            padding: 12px 24px;
            background: #fbbf24;
            color: #1f2937;
            border: none;
            border-radius: 8px;
            font-weight: 700;
            cursor: pointer;
        }

        footer { background: #1f2937; color: #e2e8f0; margin-top: 80px; padding: 50px 0 30px; }
    </style>
</head>
<body class="product-page">

    <!-- NAVBAR -->
    <header>
        <nav>
            <div class="nav-left">
                <div class="navButtonLogo" onclick="location.href='index2.php'">
                    <img src="./Images/logo.png" alt="HARDVERAPRO" style="height: 45px;">
                </div>
                <div class="navButton">PROHARDVER!</div>
                <div class="navButton">Mobilarena</div>
            </div>

            <div class="nav-right">
                <?php if(isset($_SESSION["user_id"])): ?>
                    <span>Bejelentkezve mint <strong><?= htmlspecialchars($_SESSION["username"]) ?></strong></span>
                    <a href="logout.php">Kijelentkezés</a>
                    <a href="add_listing.php">+ Hirdetés feladása</a>
                    <div class="user-icon">
                        <i class="fa-solid fa-user"></i>
                    </div>
                <?php else: ?>
                    <a href="login.php">Bejelentkezés</a>
                    <a href="register.php">Regisztráció</a>
                <?php endif; ?>
            </div>
        </nav>
    </header>

    <main class="product-content">
        <div class="product-header">
            <a href="index2.php" class="back-button">
                <i class="fa-solid fa-arrow-left"></i> Vissza a főoldalra
            </a>
            <h1><?= htmlspecialchars($row['title']) ?></h1>
        </div>

        <div class="product-main">
            <!-- BAL OLDAL -->
            <div class="product-left">
                <div class="info-bar">
                    <span><i class="fa-solid fa-user"></i> <?= htmlspecialchars($row['username']) ?></span>
                    <span><i class="fa-solid fa-clock"></i> <?= date("Y.m.d. H:i", strtotime($row['created_at'])) ?></span>
                    <span><i class="fa-solid fa-location-dot"></i> <?= htmlspecialchars($row['location_city'] ?? 'Budapest') ?></span>
                    <?php if (!empty($row['category_name'])): ?>
                        <span><i class="fa-solid fa-tag"></i> <?= htmlspecialchars($row['category_name']) ?></span>
                    <?php endif; ?>
                </div>

                <div class="product-image-box">
                    <div class="product-carousel" data-images='<?= htmlspecialchars(json_encode($images)) ?>'>
                        <button class="nav prev">‹</button>
                        <img src="<?= htmlspecialchars($images[0]) ?>" class="carousel-img" alt="<?= htmlspecialchars($row['title']) ?>">
                        <button class="nav next">›</button>
                    </div>
                </div>

                <div class="description-box">
                    <div class="price-large"><?= number_format($row['price'], 0, ",", " ") ?> Ft</div>
                    <div class="details-grid">
                        <p><strong>Állapot:</strong> <?= htmlspecialchars($row['condition'] ?? 'Új') ?></p>
                        <p><strong>Szállítás:</strong> <?= htmlspecialchars($row['shipping'] ?? 'Személyesen / Posta') ?></p>
                        <p><strong>Garancia:</strong> <?= htmlspecialchars($row['warranty'] ?? 'Nincs') ?></p>
                    </div>
                    <hr style="border:none; height:1px; background:#e5e7eb; margin:25px 0;">
                    <div style="line-height:1.75; font-size:15.2px; color:#1f2937;">
                        <?= nl2br(htmlspecialchars($row['description'] ?? 'Nincs leírás.')) ?>
                    </div>
                </div>

                <!-- További hirdetések -->
                <div class="other-listings-box">
                    <h3>Az eladó további hirdetései (<?= count($other_listings) ?> db)</h3>
                    <div class="other-grid" style="display:grid; grid-template-columns:repeat(auto-fill, minmax(170px,1fr)); gap:16px;">
                        <?php foreach ($other_listings as $ol): ?>
                            <a href="termek.php?id=<?= $ol['id'] ?>" class="small-card" style="background:white;border:1px solid #e5e7eb;border-radius:10px;padding:8px;text-decoration:none;color:inherit;">
                                <img src="./Images/logo.png" style="width:100%;height:110px;object-fit:cover;border-radius:8px;">
                                <div style="font-weight:600;margin-top:8px;"><?= htmlspecialchars($ol['title']) ?></div>
                                <div style="color:#e01212;font-weight:700;"><?= number_format($ol['price'], 0, ",", " ") ?> Ft</div>
                            </a>
                        <?php endforeach; ?>
                        <?php if(empty($other_listings)): ?>
                            <p>Az eladónak nincs további hirdetése.</p>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- HOZZÁSZÓLÁSOK -->
                <div class="comments-section">
                    <h3><i class="fa-solid fa-comments"></i> Hirdetés hozzászólásai</h3>
                    
                    <?php foreach ($comments as $comm): ?>
                        <div class="comment" style="background:#f8fafc; padding:18px; margin-bottom:18px; border-radius:8px; border-left:5px solid #fbbf24;">
                            <strong><?= htmlspecialchars($comm['username']) ?></strong> 
                            <small style="color:#64748b;">· <?= date("Y.m.d. H:i", strtotime($comm['created_at'])) ?></small>
                            <p style="margin-top:10px;"><?= nl2br(htmlspecialchars($comm['comment'])) ?></p>
                        </div>
                    <?php endforeach; ?>

                    <?php if (empty($comments)): ?>
                        <p style="color:#64748b; font-style:italic;">Még nincs hozzászólás.</p>
                    <?php endif; ?>

                    <div class="comment-form">
                        <h4>Írj hozzászólást</h4>
                        <form action="add_comment.php" method="POST">
                            <input type="hidden" name="listing_id" value="<?= $id ?>">
                            <textarea name="comment" placeholder="Írd meg a véleményedet..." required></textarea>
                            <button type="submit">Hozzászólás küldése</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- JOBB OLDAL -->
            <div class="product-sidebar">
                <div class="sidebar-box warning">
                    <h3><i class="fa-solid fa-triangle-exclamation"></i> Fontos tudnod!</h3>
                    <p>Soha ne utalj előre! Probléma esetén használd a Jelentem gombot.</p>
                </div>

                <div class="sidebar-box">
                    <h3>Az eladó adatai</h3>
                    <div style="display:flex; align-items:center; gap:16px; margin:18px 0;">
                        <img src="./Images/profie-icon.gif" style="width:60px;height:60px;border-radius:50%;border:3px solid #fbbf24;">
                        <div>
                            <strong><?= htmlspecialchars($row['username']) ?></strong><br>
                            <small>191 pozitív értékelés</small>
                        </div>
                    </div>
                    <button onclick="alert('Üzenetküldés később...')" class="msg-btn" style="background:#fbbf24;color:#111827;width:100%;padding:16px;border:none;border-radius:10px;font-weight:700;margin-bottom:15px;">
                        <i class="fa-solid fa-envelope"></i> Privát üzenet
                    </button>
                    <div class="action-buttons">
                        <button onclick="alert('Hirdetés jelentve!')" class="btn-jelentem">Jelentem</button>
                        <button onclick="alert('Köszönjük az értékelést!')" class="btn-ertekel">Értékelem</button>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <footer>
        <div style="max-width:1280px;margin:auto;padding:0 20px;display:grid;grid-template-columns:2fr 1fr 1fr 1fr;gap:40px;color:#e2e8f0;">
            <div><h4>Hardverapro</h4><p>A legnagyobb magyar hardver piactér.</p></div>
            <div><h4>Navigáció</h4><a href="index2.php" style="color:#e2e8f0;">Főoldal</a></div>
            <div><h4>Szolgáltatások</h4><a href="#" style="color:#e2e8f0;">ProHardver!</a></div>
            <div><h4>Jogi</h4><a href="#" style="color:#e2e8f0;">Adatvédelem</a></div>
        </div>
        <div style="text-align:center;margin-top:40px;padding-top:20px;border-top:1px solid #334155;color:#94a3b8;">
            © <?= date("Y") ?> Hardverapro • Minden jog fenntartva
        </div>
    </footer>

    <script>
    // Carousel
    document.querySelectorAll(".product-carousel").forEach(carousel => {
        const images = JSON.parse(carousel.dataset.images || '[]');
        const img = carousel.querySelector("img");
        let index = 0;
        const prev = carousel.querySelector(".prev");
        const next = carousel.querySelector(".next");

        function update() { 
            if (images.length > 0) img.src = images[index]; 
        }

        next.addEventListener("click", () => { 
            index = (index + 1) % images.length; 
            update(); 
        });
        prev.addEventListener("click", () => { 
            index = (index - 1 + images.length) % images.length; 
            update(); 
        });
    });
    </script>
</body>
</html>