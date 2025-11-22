<?php
require_once '../includes/config.php';
require_once '../includes/auth.php';

if (!isLoggedIn()) {
    http_response_code(401);
    echo json_encode(['error' => 'Bejelentkezés szükséges']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $order_id = $_POST['order_id'] ?? '';
    
    if (empty($order_id)) {
        http_response_code(400);
        echo json_encode(['error' => 'Hiányzó rendelés ID']);
        exit;
    }
    
    $db = getDB();
    $user_id = $_SESSION['user_id'];
    
    try {
        // Ellenőrizzük, hogy a rendelés a felhasználóhoz tartozik-e
        $stmt = $db->prepare("SELECT * FROM orders WHERE id = ? AND user_id = ?");
        $stmt->execute([$order_id, $user_id]);
        $order = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if (!$order) {
            http_response_code(404);
            echo json_encode(['error' => 'Rendelés nem található']);
            exit;
        }
        
        // Csak függőben lévő rendeléseket lehet lemondani
        if ($order['status'] !== 'pending') {
            http_response_code(400);
            echo json_encode(['error' => 'Csak függőben lévő rendeléseket lehet lemondani']);
            exit;
        }
        
        // Rendelés státuszának módosítása "cancelled"-re
        $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ?");
        $stmt->execute([$order_id]);
        
        // Visszaadjuk a könyveket a készletbe
        $stmt = $db->prepare("
            SELECT oi.book_id, oi.quantity 
            FROM order_items oi 
            WHERE oi.order_id = ?
        ");
        $stmt->execute([$order_id]);
        $order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        foreach ($order_items as $item) {
            $stmt = $db->prepare("UPDATE books SET stock = stock + ? WHERE id = ?");
            $stmt->execute([$item['quantity'], $item['book_id']]);
        }
        
        echo json_encode([
            'success' => true, 
            'message' => 'Rendelés sikeresen lemondva!'
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