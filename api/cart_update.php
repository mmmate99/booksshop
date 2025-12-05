<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Bejelentkezés szükséges']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $book_id = $_POST['book_id'] ?? '';
    $quantity = intval($_POST['quantity'] ?? 1);
    
    if (empty($book_id) || $quantity < 0) {
        http_response_code(400);
        echo json_encode(['error' => 'Érvénytelen adatok']);
        exit;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    try { 
        if ($quantity === 0) {
            // Elem eltávolítása
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND book_id = ?");
            $stmt->execute([$user_id, $book_id]);
            $message = 'Termék eltávolítva a kosárból';
        } else {
            // Mennyiség frissítése
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?");
            $stmt->execute([$quantity, $user_id, $book_id]);
            $message = 'Kosár frissítve';
        }
        
        echo json_encode(['success' => true, 'message' => $message]);
        
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(['error' => 'Adatbázis hiba: ' . $e->getMessage()]);
    }
}
?>