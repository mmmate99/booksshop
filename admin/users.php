<?php
require_once 'includes/admin_header.php';

$db = getDB();
$message = '';

// Felhasználó törlése
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    
    // Ne töröljük az admin felhasználót
    if ($delete_id != 1) {
        $stmt = $db->prepare("DELETE FROM users WHERE id = ?");
        $stmt->execute([$delete_id]);
        $message = 'Felhasználó sikeresen törölve!';
    } else {
        $message = 'Az admin felhasználó nem törölhető!';
    }
}

// Felhasználók listázása
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM users WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY created_at DESC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$users = $stmt->fetchAll(PDO::FETCH_ASSOC);
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
                        <a class="nav-link" href="orders.php">
                            <i class="bi bi-receipt"></i>
                            Rendelések
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link active" href="users.php">
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
                <h1 class="h2">Felhasználók kezelése</h1>
            </div>

            <?php if ($message): ?>
            <div class="alert alert-info alert-dismissible fade show">
                <?php echo $message; ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
            <?php endif; ?>

            <!-- Keresés -->
            <div class="card mb-4">
                <div class="card-body">
                    <form method="GET">
                        <div class="row">
                            <div class="col-md-8">
                                <input type="text" name="search" class="form-control" 
                                       placeholder="Keresés név vagy email szerint..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">Keresés</button>
                                <a href="users.php" class="btn btn-outline-secondary">Összes</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Felhasználók táblázata -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>ID</th>
                                    <th>Név</th>
                                    <th>Email</th>
                                    <th>Cím</th>
                                    <th>Telefon</th>
                                    <th>Regisztráció</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($users)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Nincsenek felhasználók
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($users as $user): ?>
                                    <tr>
                                        <td><?php echo $user['id']; ?></td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                                            <?php if ($user['email'] === 'admin@konyvesbolt.hu'): ?>
                                                <span class="badge bg-danger ms-1">Admin</span>
                                            <?php endif; ?>
                                        </td>
                                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                                        <td>
                                            <?php if (!empty($user['address'])): ?>
                                                <small><?php echo htmlspecialchars(substr($user['address'], 0, 50)); ?>...</small>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php echo !empty($user['phone']) ? htmlspecialchars($user['phone']) : '<span class="text-muted">-</span>'; ?>
                                        </td>
                                        <td>
                                            <small><?php echo date('Y.m.d.', strtotime($user['created_at'])); ?></small>
                                        </td>
                                        <td>
                                            <?php if ($user['email'] !== 'admin@konyvesbolt.hu'): ?>
                                                <a href="?delete=<?php echo $user['id']; ?>" 
                                                   class="btn btn-sm btn-outline-danger"
                                                   onclick="return confirm('Biztosan törölni szeretnéd ezt a felhasználót?')">
                                                    <i class="bi bi-trash"></i> Törlés
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted">-</span>
                                            <?php endif; ?>
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