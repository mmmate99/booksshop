<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$order_id = $_GET['id'] ?? '';

if (empty($order_id)) {
    redirect('orders.php');
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Rendelés lemondása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['cancel_order'])) {
    $stmt = $db->prepare("UPDATE orders SET status = 'cancelled' WHERE id = ? AND user_id = ? AND status = 'pending'");
    $stmt->execute([$order_id, $user_id]);
    
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
    
    $message = 'Rendelés sikeresen lemondva!';
}

// Rendelés adatainak lekérése
$stmt = $db->prepare("
    SELECT o.*, u.name as user_name, u.email as user_email, u.address, u.phone 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE o.id = ? AND (o.user_id = ? OR ? = (SELECT id FROM users WHERE email = 'admin@konyvesbolt.hu'))
");
$stmt->execute([$order_id, $user_id, $user_id]);
$order = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$order) {
    redirect('orders.php');
}

// Rendelés tételek lekérése
$stmt = $db->prepare("
    SELECT oi.*, b.title, b.author, b.image 
    FROM order_items oi 
    JOIN books b ON oi.book_id = b.id 
    WHERE oi.order_id = ?
");
$stmt->execute([$order_id]);
$order_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

$status_texts = [
    'pending' => 'Függőben',
    'processing' => 'Feldolgozás alatt',
    'shipped' => 'Kiszállítva',
    'delivered' => 'Kézbesítve',
    'cancelled' => 'Törölve'
];

$status_classes = [
    'pending' => 'warning',
    'processing' => 'info',
    'shipped' => 'primary',
    'delivered' => 'success',
    'cancelled' => 'danger'
];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelés részletei - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <nav aria-label="breadcrumb">
            <ol class="breadcrumb">
                <li class="breadcrumb-item"><a href="index.php">Könyvek</a></li>
                <li class="breadcrumb-item"><a href="orders.php">Rendeléseim</a></li>
                <li class="breadcrumb-item active">Rendelés #<?php echo $order['order_number']; ?></li>
            </ol>
        </nav>

        <?php if (isset($message)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo $message; ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">Rendelés információ</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Rendelés adatai</h5>
                                <p><strong>Rendelés szám:</strong> #<?php echo $order['order_number']; ?></p>
                                <p><strong>Rendelés dátuma:</strong> <?php echo date('Y. m. d. H:i', strtotime($order['created_at'])); ?></p>
                                <p><strong>Státusz:</strong> 
                                    <span class="badge bg-<?php echo $status_classes[$order['status']]; ?>">
                                        <?php echo $status_texts[$order['status']]; ?>
                                    </span>
                                </p>
                            </div>
                            <div class="col-md-6">
                                <h5>Fizetési adatok</h5>
                                <p><strong>Fizetési mód:</strong> 
                                    <?php 
                                    $payment_methods = [
                                        'cash_on_delivery' => 'Utánvét',
                                        'bank_transfer' => 'Banki átutalás',
                                        'credit_card' => 'Bankkártya'
                                    ];
                                    echo $payment_methods[$order['payment_method']] ?? $order['payment_method'];
                                    ?>
                                </p>
                                <p><strong>Összeg:</strong> <span class="h5 text-primary"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</span></p>
                            </div>
                        </div>
                        
                        <?php if ($order['status'] === 'pending' && !isAdmin()): ?>
                        <div class="mt-4">
                            <form method="POST">
                                <input type="hidden" name="cancel_order" value="1">
                                <button type="submit" class="btn btn-danger" 
                                        onclick="return confirm('Biztosan le szeretnéd mondani ezt a rendelést?')">
                                    <i class="bi bi-x-circle"></i> Rendelés lemondása
                                </button>
                            </form>
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Rendelt könyvek</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($order_items as $item): ?>
                            <div class="row align-items-center mb-3 pb-3 border-bottom">
                                <div class="col-md-2">
                                    <img src="<?php echo $item['image']; ?>" 
                                         alt="<?php echo htmlspecialchars($item['title']); ?>" 
                                         class="img-fluid rounded" style="max-height: 80px;">
                                </div>
                                <div class="col-md-6">
                                    <h6 class="mb-1"><?php echo htmlspecialchars($item['title']); ?></h6>
                                    <p class="text-muted mb-1"><?php echo htmlspecialchars($item['author']); ?></p>
                                </div>
                                <div class="col-md-2 text-center">
                                    <span class="text-muted"><?php echo $item['quantity']; ?> db</span>
                                </div>
                                <div class="col-md-2 text-end">
                                    <strong><?php echo number_format($item['unit_price'] * $item['quantity'], 0, ',', ' '); ?> Ft</strong>
                                    <br>
                                    <small class="text-muted"><?php echo number_format($item['unit_price'], 0, ',', ' '); ?> Ft/db</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <div class="row mt-3">
                            <div class="col-md-8"></div>
                            <div class="col-md-4">
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Könyvek ára:</span>
                                    <span><?php echo number_format($order['total_amount'] - 1490, 0, ',', ' '); ?> Ft</span>
                                </div>
                                <div class="d-flex justify-content-between mb-2">
                                    <span>Szállítás:</span>
                                    <span>1 490 Ft</span>
                                </div>
                                <hr>
                                <div class="d-flex justify-content-between">
                                    <strong>Összesen:</strong>
                                    <strong class="h5 text-primary"><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</strong>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Szállítási adatok</h5>
                    </div>
                    <div class="card-body">
                        <h6><?php echo htmlspecialchars($order['user_name']); ?></h6>
                        <p class="mb-2"><?php echo nl2br(htmlspecialchars($order['shipping_address'])); ?></p>
                        <?php if (!empty($order['email'])): ?>
                            <p class="mb-1"><i class="bi bi-envelope me-2"></i><?php echo $order['user_email']; ?></p>
                        <?php endif; ?>
                        <?php if (!empty($order['phone'])): ?>
                            <p class="mb-0"><i class="bi bi-telephone me-2"></i><?php echo htmlspecialchars($order['phone']); ?></p>
                        <?php endif; ?>
                    </div>
                </div>
                
                <div class="card mt-4">
                    <div class="card-body text-center">
                        <a href="orders.php" class="btn btn-outline-primary me-2">
                            <i class="bi bi-arrow-left"></i> Vissza a rendelésekhez
                        </a>
                        <?php if (isAdmin()): ?>
                            <a href="admin/orders.php" class="btn btn-primary mt-2 mt-md-0">
                                <i class="bi bi-speedometer2"></i> Admin Panel
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>