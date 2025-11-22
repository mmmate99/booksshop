<?php
require_once '../includes/config.php';

header('Content-Type: application/json');

$db = getDB();

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $id = $_GET['id'] ?? '';
    
    if (!empty($id)) {
        // Egy könyv részletei
        $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
        $stmt->execute([$id]);
        $book = $stmt->fetch(PDO::FETCH_ASSOC);
        
        if ($book) {
            echo json_encode($book);
        } else {
            http_response_code(404);
            echo json_encode(['error' => 'Könyv nem található']);
        }
    } else {
        // Könyvek listája szűréssel
        $search = $_GET['search'] ?? '';
        $category = $_GET['category'] ?? '';
        $page = intval($_GET['page'] ?? 1);
        $limit = intval($_GET['limit'] ?? 12);
        $offset = ($page - 1) * $limit;
        
        $query = "SELECT * FROM books WHERE 1=1";
        $params = [];
        
        if (!empty($search)) {
            $query .= " AND (title LIKE ? OR author LIKE ?)";
            $params[] = "%$search%";
            $params[] = "%$search%";
        }
        
        if (!empty($category)) {
            $query .= " AND category = ?";
            $params[] = $category;
        }
        
        // Összes találat száma
        $countStmt = $db->prepare(str_replace('*', 'COUNT(*)', $query));
        $countStmt->execute($params);
        $total = $countStmt->fetchColumn();
        
        // Adatok lekérése
        $query .= " ORDER BY title ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        $stmt = $db->prepare($query);
        $stmt->execute($params);
        $books = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        echo json_encode([
            'books' => $books,
            'total' => $total,
            'page' => $page,
            'total_pages' => ceil($total / $limit)
        ]);
    }
}
?>