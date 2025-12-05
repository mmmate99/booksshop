<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Kosár elemek lekérése
$stmt = $db->prepare("
    SELECT c.*, b.title, b.author, b.price, b.stock, b.image 
    FROM cart c 
    JOIN books b ON c.book_id = b.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}

// Mennyiség módosítás
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $book_id = $_POST['book_id'] ?? '';
    
    if ($action === 'update') {
        $quantity = intval($_POST['quantity'] ?? 1);
        if ($quantity > 0) {
            $stmt = $db->prepare("UPDATE cart SET quantity = ? WHERE user_id = ? AND book_id = ?");
            $stmt->execute([$quantity, $user_id, $book_id]);
        }
    } elseif ($action === 'remove') {
        $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ? AND book_id = ?");
        $stmt->execute([$user_id, $book_id]);
    }
    
    redirect('cart.php');
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kosár - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Kosár</h1>
        
        <?php if (empty($cart_items)): ?>
            <div class="text-center py-5">
                <i class="bi bi-cart display-1 text-muted"></i>
                <h3 class="mt-3">A kosarad üres</h3>
                <p class="text-muted">Vásárolj néhány könyvet a boltból!</p>
                <a href="index.php" class="btn btn-primary">Vásárlás folytatása</a>
            </div>
        <?php else: ?>
            <div class="row">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body">
                            <?php foreach($cart_items as $item): ?>
                                <div class="row align-items-center mb-4 pb-4 border-bottom">
                                    <div class="col-md-2">
                                        <img src="<?php echo $item['image']; ?>" 
                                             alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                             class="img-fluid rounded">
                                    </div>
                                    <div class="col-md-4">
                                        <h5><?php echo htmlspecialchars($item['title']); ?></h5>
                                        <p class="text-muted"><?php echo htmlspecialchars($item['author']); ?></p>
                                        <span class="text-primary fw-bold"><?php echo number_format($item['price'], 0, ',', ' '); ?> Ft</span>
                                    </div>
                                    <div class="col-md-3">
                                        <form method="POST" class="d-flex align-items-center">
                                            <input type="hidden" name="book_id" value="<?php echo $item['book_id']; ?>">
                                            <input type="number" name="quantity" 
                                                   value="<?php echo $item['quantity']; ?>" 
                                                   min="1" max="<?php echo $item['stock']; ?>"
                                                   class="form-control me-2" style="width: 80px;">
                                            <button type="submit" name="action" value="update" 
                                                    class="btn btn-outline-primary btn-sm">Frissítés</button>
                                        </form>
                                    </div>
                                    <div class="col-md-2 text-center">
                                        <strong><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Ft</strong>
                                    </div>
                                    <div class="col-md-1">
                                        <form method="POST">
                                            <input type="hidden" name="book_id" value="<?php echo $item['book_id']; ?>">
                                            <button type="submit" name="action" value="remove" 
                                                    class="btn btn-outline-danger btn-sm">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
                 
                <div class="col-lg-4">
                    <div class="card">
                        <div class="card-body">
                            <h5 class="card-title">Összegzés</h5>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Könyvek ára:</span>
                                <span><?php echo number_format($total, 0, ',', ' '); ?> Ft</span>
                            </div>
                            <div class="d-flex justify-content-between mb-2">
                                <span>Szállítás:</span>
                                <span>1 490 Ft</span>
                            </div>
                            <hr>
                            <div class="d-flex justify-content-between mb-3">
                                <strong>Összesen:</strong>
                                <strong><?php echo number_format($total + 1490, 0, ',', ' '); ?> Ft</strong>
                            </div>
                            <a href="checkout.php" class="btn btn-primary w-100">Tovább a fizetéshez</a>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>