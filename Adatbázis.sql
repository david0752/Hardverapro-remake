-- =====================================================
-- HardverApró‑szerű adatbázis (újraépítéshez)
-- =====================================================
-- 1. Készítsünk egy új adatbázist (csak MySQL/MariaDB)
CREATE DATABASE IF NOT EXISTS hardverapro CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hardverapro;
-- 2. Felhasználók tábla
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    username VARCHAR(50) NOT NULL UNIQUE,
    email VARCHAR(100) NOT NULL UNIQUE,
    password_hash VARCHAR(255) NOT NULL,
    display_name VARCHAR(100),
    city VARCHAR(100),
    phone VARCHAR(30),
    is_verified TINYINT(1) DEFAULT 0,
    is_banned TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME DEFAULT NULL ON UPDATE NOW(),
    INDEX idx_username (username),
    INDEX idx_email (email)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 3. Kategóriák (pl. "CPU", "RAM", "GPU" stb.)
CREATE TABLE categories (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL UNIQUE,
    parent_id INT UNSIGNED DEFAULT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (parent_id) REFERENCES categories(id) ON DELETE
    SET NULL,
        INDEX idx_slug (slug),
        INDEX idx_parent (parent_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 4. Hirdetések tábla
CREATE TABLE listings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    title VARCHAR(200) NOT NULL,
    description TEXT,
    price DECIMAL(12, 2) NOT NULL,
    currency VARCHAR(3) DEFAULT 'HUF',
    category_id INT UNSIGNED NOT NULL,
    status ENUM('active', 'inactive', 'sold', 'deleted') DEFAULT 'active',
    click_count INT UNSIGNED DEFAULT 0,
    location_city VARCHAR(100),
    location_region VARCHAR(100),
    created_at DATETIME NOT NULL DEFAULT NOW(),
    updated_at DATETIME DEFAULT NULL ON UPDATE NOW(),
    deleted_at DATETIME DEFAULT NULL,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE RESTRICT,
    INDEX idx_user_id (user_id),
    INDEX idx_category_id (category_id),
    INDEX idx_status (status),
    INDEX idx_created_at (created_at),
    INDEX idx_deleted_at (deleted_at),
    INDEX idx_title (title(50))
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 5. Hirdetés képei
CREATE TABLE listing_images (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    image_path VARCHAR(500) NOT NULL,
    is_cover TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    INDEX idx_listing_id (listing_id),
    INDEX idx_is_cover (is_cover)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 6. Beszélgetések / üzenetek
CREATE TABLE conversations (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED NOT NULL,
    buyer_id INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_listing_id (listing_id),
    INDEX idx_buyer_id (buyer_id),
    INDEX idx_seller_id (seller_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 7. Üzenetek tábla
CREATE TABLE messages (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    conversation_id INT UNSIGNED NOT NULL,
    sender_id INT UNSIGNED NOT NULL,
    text TEXT NOT NULL,
    is_read TINYINT(1) DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (conversation_id) REFERENCES conversations(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_conversation_id (conversation_id),
    INDEX idx_sender_id (sender_id),
    INDEX idx_created_at (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 8. Értékelések / jelölések (eladóra vonatkozó)
CREATE TABLE ratings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    buyer_id INT UNSIGNED NOT NULL,
    seller_id INT UNSIGNED NOT NULL,
    listing_id INT UNSIGNED NOT NULL,
    score TINYINT NOT NULL CHECK (
        score >= 1
        AND score <= 5
    ),
    comment TEXT,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (buyer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (seller_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    UNIQUE KEY uk_buyer_seller_listing (buyer_id, seller_id, listing_id),
    INDEX idx_seller_id (seller_id),
    INDEX idx_created_at (created_at)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 9. Jelöltek / kedvencek (favorit hirdetések)
CREATE TABLE favorites (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    listing_id INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE CASCADE,
    UNIQUE KEY uk_user_listing (user_id, listing_id),
    INDEX idx_listing_id (listing_id)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 10. Jelentések / visszajelzések a hirdetésekre
CREATE TABLE reports (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    listing_id INT UNSIGNED,
    reporter_id INT UNSIGNED NOT NULL,
    reason TEXT NOT NULL,
    status ENUM('pending', 'reviewed', 'rejected', 'resolved') DEFAULT 'pending',
    created_at DATETIME NOT NULL DEFAULT NOW(),
    FOREIGN KEY (listing_id) REFERENCES listings(id) ON DELETE
    SET NULL,
        FOREIGN KEY (reporter_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_listing_id (listing_id),
        INDEX idx_reporter_id (reporter_id),
        INDEX idx_status (status)
) ENGINE = InnoDB DEFAULT CHARSET = utf8mb4;
-- 11. Kategóriák példák (opcionális minta adat)
INSERT INTO categories (name, slug, parent_id)
VALUES ('CPU', 'cpu', NULL),
    ('RAM', 'ram', NULL),
    ('GPU', 'gpu', NULL),
    ('Alaplap', 'alaplap', NULL),
    ('Tápegység', 'tagegyseg', NULL),
    ('Házi készlet', 'hazikomplet', NULL);