<?php
session_start();

// Adatbázis kapcsolat
define('DB_HOST', 'sql101.infinityfree.com');
define('DB_USER', 'if0_39931198');
define('DB_PASS', 'H3F92sjVlbjqal');
define('DB_NAME', 'if0_39931198_bookshop');

// Alkalmazás beállítások
define('APP_NAME', 'Könyvesbolt');

// Automatikus URL detektálás - működik lokálisan és hoston is
$is_localhost = in_array($_SERVER['HTTP_HOST'], ['localhost', '127.0.0.1']);
if ($is_localhost) {
    // Lokális fejlesztés
    define('APP_URL', 'http://localhost/books-php-project');
} else {
    // Éles host
    $protocol = isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http";
    $host = $_SERVER['HTTP_HOST'];
    $path = rtrim(dirname($_SERVER['PHP_SELF']), '/\\');
    define('APP_URL', $protocol . '://' . $host . $path);
}

// Adatbázis kapcsolat létrehozása
function getDB() {
    static $db = null;
    if ($db === null) { 
        try {
            $db = new PDO(
                "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=utf8",
                DB_USER,
                DB_PASS,
                [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
            );
        } catch(PDOException $e) {
            die("Adatbázis kapcsolat hiba: " . $e->getMessage());
        }
    }
    return $db;
}

// Alap függvények
function redirect($url) {
    header("Location: " . APP_URL . "/" . ltrim($url, '/'));
    exit;
}

function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdmin() {
    return isset($_SESSION['user_email']) && $_SESSION['user_email'] === 'admin@konyvesbolt.hu';
}
?>