<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

header('Content-Type: application/json');

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Bejelentkezés szükséges']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? '';
    
    // Hibakezelés - ellenőrizzük, hogy van-e book_id
    if (empty($book_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Hiányzó könyv ID']);
        exit;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    try {
        // Ellenőrizzük, hogy létezik-e a könyv és van-e készleten
        $stmt = $db->prepare("SELECT stock FROM books WHERE id = ?");
        $stmt->execute([$book_id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$book) {
            http_response_code(404);
            echo json_encode(['error' => 'A könyv nem található']);
            exit;
        }
        
        if ($book['stock'] <= 0) {
            http_response_code(400);
            echo json_encode(['error' => 'A könyv jelenleg nincs raktáron']);
            exit;
        }
        
        // Ellenőrizzük, hogy már van-e a kosárban
        $stmt = $db->prepare("SELECT * FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
        $existing = $stmt->fetch();
        
        if ($existing) {
            // Mennyiség növelése, de csak ha van elég készlet
            $new_quantity = $existing['quantity'] + 1;
            if ($new_quantity <= $book['stock']) {
                $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?");
                $stmt->execute([$new_quantity, $user_id, $book_id]);
                $message = 'Könyv hozzáadva a kosárhoz (mennyiség frissítve)';
            } else {
                http_response_code(400);
                echo json_encode(['error' => 'Nincs elég készlet']);
                exit;
            }
        } else {
            // Új elem hozzáadása
            $stmt = $db->prepare("INSERT INTO cart (user_id, book_id, quantity) VALUES (?, ?, 1)");
            $stmt->execute([$user_id, $book_id]);
            $message = 'Könyv hozzáadva a kosárhoz';
        }
        
        // Kosár számláló frissítése
        $stmt = $db->prepare("SELECT SUM(quantity) as total FROM cart WHERE user_id = ?");
        $stmt->execute([$user_id]);
        $cart_count = $stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0;
        $_SESSION['cart_count'] = $cart_count;
        
        echo json_encode([
            'success' => true, 
            'message' => $message,
            'cart_count' => $cart_count
        ]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Adatbázis hiba: ' . $e->getMessage()]);
    }
} else {
    http_response_code(405);
    echo json_encode(['error' => 'Csak POST kérés engedélyezett']);
}
?>