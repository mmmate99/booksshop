<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

$db = getDB();
$user_id = $_SESSION['user_id'];

// Rendelések lekérése
$stmt = $db->prepare("
    SELECT o.*, COUNT(oi.id) as item_count 
    FROM orders o 
    LEFT JOIN order_items oi ON o.id = oi.order_id 
    WHERE o.user_id = ? 
    GROUP BY o.id 
    ORDER BY o.created_at DESC
");
$stmt->execute([$user_id]);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendeléseim - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-4">
        <h1 class="mb-4">Rendeléseim</h1>
        
        <?php if (empty($orders)): ?>
            <div class="text-center py-5">
                <i class="bi bi-receipt display-1 text-muted"></i>
                <h3 class="mt-3">Még nincs rendelésed</h3>
                <p class="text-muted">Vásárolj néhány könyvet a boltból!</p>
                <a href="index.php" class="btn btn-primary">Vásárlás folytatása</a>
            </div>
        <?php else: ?>
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-hover">
                            <thead>
                                <tr>
                                    <th>Rendelés szám</th>
                                    <th>Dátum</th>
                                    <th>Összeg</th>
                                    <th>Státusz</th>
                                    <th>Tételek</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['order_number']; ?></strong>
                                        </td>
                                        <td>
                                            <?php echo date('Y.m.d. H:i', strtotime($order['created_at'])); ?>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</strong>
                                        </td>
                                        <td>
                                            <?php
                                            $status_classes = [
                                                'pending' => 'warning',
                                                'processing' => 'info', 
                                                'shipped' => 'primary',
                                                'delivered' => 'success',
                                                'cancelled' => 'danger'
                                            ];
                                            $status_texts = [
                                                'pending' => 'Függőben',
                                                'processing' => 'Feldolgozás alatt',
                                                'shipped' => 'Kiszállítva',
                                                'delivered' => 'Kézbesítve',
                                                'cancelled' => 'Törölve'
                                            ];
                                            ?>
                                            <span class="badge bg-<?php echo $status_classes[$order['status']]; ?>">
                                                <?php echo $status_texts[$order['status']]; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php echo $order['item_count']; ?> db
                                        </td>
                                        <td>
                                            <a href="order_details.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-outline-primary btn-sm">Részletek</a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html> 