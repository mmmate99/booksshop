<?php
require_once 'includes/admin_header.php';

$db = getDB();

// Statisztikák
$users_count = $db->query("SELECT COUNT(*) FROM users")->fetchColumn();
$books_count = $db->query("SELECT COUNT(*) FROM books")->fetchColumn();
$orders_count = $db->query("SELECT COUNT(*) FROM orders")->fetchColumn();
$total_revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) FROM orders WHERE status = 'delivered'")->fetchColumn();

// Legújabb rendelések
$recent_orders = $db->query("
    SELECT o.*, u.name as user_name 
    FROM orders o 
    JOIN users u ON o.user_id = u.id 
    ORDER BY o.created_at DESC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC);

// Alacsony készletű könyvek
$low_stock_books = $db->query("
    SELECT * FROM books 
    WHERE stock <= 5 AND stock > 0 
    ORDER BY stock ASC 
    LIMIT 5
")->fetchAll(PDO::FETCH_ASSOC); 
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
                        <a class="nav-link active" href="index.php">
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
                        <a class="nav-link" href="orders.php">
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
                <h1 class="h2">Áttekintés</h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <span class="text-muted"><?php echo date('Y. m. d.'); ?></span>
                </div>
            </div>

            <!-- Statisztikák -->
            <div class="row">
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-primary shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                        Felhasználók
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $users_count; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-people fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-success shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-success text-uppercase mb-1">
                                        Könyvek
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $books_count; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-book fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-info shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-info text-uppercase mb-1">
                                        Rendelések
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo $orders_count; ?></div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-receipt fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="col-xl-3 col-md-6 mb-4">
                    <div class="card border-left-warning shadow h-100 py-2">
                        <div class="card-body">
                            <div class="row no-gutters align-items-center">
                                <div class="col mr-2">
                                    <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                        Bevétel
                                    </div>
                                    <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($total_revenue, 0, ',', ' '); ?> Ft</div>
                                </div>
                                <div class="col-auto">
                                    <i class="bi bi-currency-exchange fa-2x text-gray-300"></i>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="row">
                <!-- Legújabb rendelések -->
                <div class="col-xl-8 col-lg-7">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Legújabb rendelések</h6>
                            <a href="orders.php" class="btn btn-sm btn-primary">Összes megtekintése</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($recent_orders)): ?>
                                <p class="text-muted">Még nincsenek rendelések.</p>
                            <?php else: ?>
                                <div class="table-responsive">
                                    <table class="table table-bordered" width="100%" cellspacing="0">
                                        <thead>
                                            <tr>
                                                <th>Rendelés szám</th>
                                                <th>Felhasználó</th>
                                                <th>Összeg</th>
                                                <th>Státusz</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php foreach($recent_orders as $order): ?>
                                                <tr>
                                                    <td>
                                                        <a href="order_details.php?id=<?php echo $order['id']; ?>">
                                                            #<?php echo $order['order_number']; ?>
                                                        </a>
                                                    </td>
                                                    <td><?php echo htmlspecialchars($order['user_name']); ?></td>
                                                    <td><?php echo number_format($order['total_amount'], 0, ',', ' '); ?> Ft</td>
                                                    <td>
                                                        <span class="badge bg-secondary"><?php echo $order['status']; ?></span>
                                                    </td>
                                                </tr>
                                            <?php endforeach; ?>
                                        </tbody>
                                    </table>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>

                <!-- Alacsony készlet -->
                <div class="col-xl-4 col-lg-5">
                    <div class="card shadow mb-4">
                        <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                            <h6 class="m-0 font-weight-bold text-primary">Alacsony készlet</h6>
                            <a href="books.php" class="btn btn-sm btn-primary">Könyvek</a>
                        </div>
                        <div class="card-body">
                            <?php if (empty($low_stock_books)): ?>
                                <p class="text-muted">Nincsenek alacsony készletű könyvek.</p>
                            <?php else: ?>
                                <div class="list-group list-group-flush">
                                    <?php foreach($low_stock_books as $book): ?>
                                        <div class="list-group-item d-flex justify-content-between align-items-center">
                                            <div>
                                                <h6 class="mb-1"><?php echo htmlspecialchars($book['title']); ?></h6>
                                                <small class="text-muted"><?php echo htmlspecialchars($book['author']); ?></small>
                                            </div>
                                            <span class="badge bg-danger rounded-pill"><?php echo $book['stock']; ?> db</span>
                                        </div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>
                        </div>
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