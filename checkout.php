<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Kosár ellenőrzése
$stmt = $db->prepare("
    SELECT c.*, b.title, b.author, b.price, b.stock 
    FROM cart c 
    JOIN books b ON c.book_id = b.id 
    WHERE c.user_id = ?
");
$stmt->execute([$user_id]);
$cart_items = $stmt->fetchAll(PDO::FETCH_ASSOC);

if (empty($cart_items)) {
    redirect('cart.php');
}

// Felhasználó adatainak lekérése
$stmt = $db->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

$total = 0;
foreach ($cart_items as $item) {
    $total += $item['price'] * $item['quantity'];
}
$shipping_cost = 1490;
$grand_total = $total + $shipping_cost;

// Rendelés leadása
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $shipping_address = trim($_POST['shipping_address'] ?? '');
    $payment_method = $_POST['payment_method'] ?? '';
    
    if (empty($shipping_address)) {
        $error = 'A szállítási cím megadása kötelező!';
    } elseif (empty($payment_method)) {
        $error = 'A fizetési mód kiválasztása kötelező!';
    } else {
        try {
            $db->beginTransaction();
            
            // Rendelés szám generálása
            $order_number = 'ORD-' . date('Ymd') . '-' . str_pad($user_id, 4, '0', STR_PAD_LEFT) . '-' . rand(1000, 9999);
            
            // Rendelés létrehozása
            $stmt = $db->prepare("
                INSERT INTO orders (user_id, order_number, total_amount, shipping_address, payment_method) 
                VALUES (?, ?, ?, ?, ?)
            ");
            $stmt->execute([$user_id, $order_number, $grand_total, $shipping_address, $payment_method]);
            $order_id = $db->lastInsertId();
            
            // Rendelés tételek hozzáadása
            foreach ($cart_items as $item) {
                $stmt = $db->prepare("
                    INSERT INTO order_items (order_id, book_id, quantity, unit_price) 
                    VALUES (?, ?, ?, ?)
                ");
                $stmt->execute([$order_id, $item['book_id'], $item['quantity'], $item['price']]);
                
                // Készlet csökkentése
                $stmt = $db->prepare("UPDATE books SET stock = stock - ? WHERE id = ?");
                $stmt->execute([$item['quantity'], $item['book_id']]);
            }
            
            // Kosár ürítése
            $stmt = $db->prepare("DELETE FROM cart WHERE user_id = ?");
            $stmt->execute([$user_id]);
            
            $db->commit();
            
            // Sikeres rendelés
            $_SESSION['order_success'] = $order_number;
            redirect('order_success.php');
            
        } catch (Exception $e) {
            $db->rollBack();
            $error = 'Hiba történt a rendelés feldolgozása során: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Fizetés - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Fizetés</h1>
        
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php endif; ?>

        <div class="row">
            <div class="col-lg-8">
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Szállítási adatok</h5>
                    </div>
                    <div class="card-body">
                        <form method="POST">
                            <div class="mb-3">
                                <label for="shipping_address" class="form-label">Szállítási cím</label>
                                <textarea class="form-control" id="shipping_address" name="shipping_address" 
                                          rows="4" required><?php echo htmlspecialchars($user['address'] ?? ''); ?></textarea>
                                <div class="form-text">
                                    Kérjük, add meg a pontos címet, ahová szeretnéd a csomagot kézbesíteni.
                                </div>
                            </div>
                            
                            <div class="mb-4">
                                <label class="form-label">Fizetési mód</label>
                                <div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="cash_on_delivery" value="cash_on_delivery" checked>
                                        <label class="form-check-label" for="cash_on_delivery">
                                            Utánvét
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="bank_transfer" value="bank_transfer">
                                        <label class="form-check-label" for="bank_transfer">
                                            Banki átutalás
                                        </label>
                                    </div>
                                    <div class="form-check">
                                        <input class="form-check-input" type="radio" name="payment_method" 
                                               id="credit_card" value="credit_card">
                                        <label class="form-check-label" for="credit_card">
                                            Bankkártya
                                        </label>
                                    </div>
                                </div>
                            </div>
                            
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                Rendelés leadása
                            </button>
                        </form>
                    </div>
                </div>
            </div>
            
            <div class="col-lg-4">
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Rendelés összegzése</h5>
                    </div>
                    <div class="card-body">
                        <?php foreach($cart_items as $item): ?>
                            <div class="d-flex justify-content-between mb-2">
                                <div>
                                    <small><?php echo htmlspecialchars($item['title']); ?></small>
                                    <br>
                                    <small class="text-muted"><?php echo $item['quantity']; ?> db × <?php echo number_format($item['price'], 0, ',', ' '); ?> Ft</small>
                                </div>
                                <div>
                                    <small><?php echo number_format($item['price'] * $item['quantity'], 0, ',', ' '); ?> Ft</small>
                                </div>
                            </div>
                        <?php endforeach; ?>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Könyvek ára:</span>
                            <span><?php echo number_format($total, 0, ',', ' '); ?> Ft</span>
                        </div>
                        
                        <div class="d-flex justify-content-between mb-2">
                            <span>Szállítás:</span>
                            <span><?php echo number_format($shipping_cost, 0, ',', ' '); ?> Ft</span>
                        </div>
                        
                        <hr>
                        
                        <div class="d-flex justify-content-between mb-3">
                            <strong>Összesen:</strong>
                            <strong><?php echo number_format($grand_total, 0, ',', ' '); ?> Ft</strong>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>