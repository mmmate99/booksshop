<?php
require_once 'includes/admin_header.php';

$db = getDB();
$message = '';

// Rendelés státusz módosítása
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['update_status'])) {
    $order_id = $_POST['order_id'];
    $new_status = $_POST['status'];
    
    $stmt = $db->prepare("UPDATE orders SET status = ? WHERE id = ?");
    $stmt->execute([$new_status, $order_id]);
    $message = 'Rendelés státusza frissítve!';
}

// Rendelések listázása
$status_filter = $_GET['status'] ?? '';
$search = $_GET['search'] ?? '';

$query = "
    SELECT o.*, u.name as user_name, u.email as user_email 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    WHERE 1=1
";
$params = [];

if (!empty($status_filter)) {
    $query .= " AND o.status = ?";
    $params[] = $status_filter;
}

if (!empty($search)) {
    $query .= " AND (o.order_number LIKE ? OR u.name LIKE ? OR u.email LIKE ?)";
    $search_term = "%$search%";
    $params[] = $search_term;
    $params[] = $search_term;
    $params[] = $search_term;
}

$query .= " ORDER BY o.created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$orders = $stmt->fetchAll(PDO::FETCH_ASSOC);

$statuses = [
    'pending' => 'Függőben',
    'processing' => 'Feldolgozás alatt',
    'shipped' => 'Kiszállítva',
    'delivered' => 'Kézbesítve',
    'cancelled' => 'Törölve'
];
?>

<div class="container-fluid">
    <div class="row">
        <!-- Sidebar -->
        <nav class="col-md-3 col-lg-2 d-md-block bg-light sidebar">
            <div class="position-sticky pt-3">
                <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
                    <span>Admin Panel</span>
                </h6>
                <ul class="nav flex-column">
                    <li class="nav-item">
                        <a class="nav-link" href="index.php">
                            <i class="bi bi-speedometer2"></i>
                            Áttekintés
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="books.php">
                            <i class="bi bi-book"></i>
                            Könyvek
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="orders.php">
                            <i class="bi bi-receipt"></i>
                            Rendelések
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="users.php">
                            <i class="bi bi-people"></i>
                            Felhasználók
                        </a>
                    </li>
                </ul>
            </div>
        </nav>

        <!-- Main content -->
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Rendelések kezelése</h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Szűrők -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-4 mb-3">
                                <label class="form-label">Státusz szerint</label>
                                <select name="status" class="form-select" onchange="this.form.submit()">
                                    <option value="">Összes rendelés</option>
                                    <?php foreach($statuses as $key => $label): ?>
                                        <option value="<?php echo $key; ?>" 
                                            <?php echo $status_filter === $key ? 'selected' : ''; ?>>
                                            <?php echo $label; ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                            <div class="col-md-6 mb-3">
                                <label class="form-label">Keresés</label>
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Rendelés szám, név vagy email..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-2 mb-3 d-flex align-items-end">
                                <button type="submit" class="btn btn-primary me-2">Keresés</button>
                                <a href="orders.php" class="btn btn-outline-secondary">Összes</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Rendelések táblázata -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Rendelés szám</th>
                                    <th>Felhasználó</th>
                                    <th>Összeg</th>
                                    <th>Státusz</th>
                                    <th>Dátum</th>
                                    <th>Fizetés</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($orders)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Nincsenek rendelések
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($orders as $order): ?>
                                    <tr>
                                        <td>
                                            <strong>#<?php echo $order['order_number']; ?></strong>
                                        </td>
                                        <td>
                                            <div>
                                                <strong><?php echo htmlspecialchars($order['user_name']); ?></strong>
                                                <br>
                                                <small class="text-muted"><?php echo $order['user_email']; ?></small>
                                            </div>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</strong>
                                        </td>
                                        <td>
                                            <form method="POST" class="d-inline">
                                                <input type="hidden" name="order_id" value="<?php echo $order['id']; ?>">
                                                <select name="status" class="form-select form-select-sm" 
                                                        onchange="this.form.submit()" style="width: auto;">
                                                    <?php foreach($statuses as $key => $label): ?>
                                                        <option value="<?php echo $key; ?>" 
                                                            <?php echo $order['status'] === $key ? 'selected' : ''; ?>>
                                                            <?php echo $label; ?>
                                                        </option>
                                                    <?php endforeach; ?>
                                                </select>
                                                <input type="hidden" name="update_status" value="1">
                                            </form>
                                        </td>
                                        <td>
                                            <small><?php echo date('Y.m.d. H:i', strtotime($order['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $order['payment_method']; ?></span>
                                        </td>
                                        <td>
                                            <a href="../order_details.php?id=<?php echo $order['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary">
                                                <i class="bi bi-eye"></i> Részletek
                                            </a>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div> 
</div>

<?php 
$no_footer = true;
include __DIR__ . '/../includes/footer.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>