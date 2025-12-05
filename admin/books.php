<?php
require_once 'includes/admin_header.php';

$db = getDB();
$message = '';

// Könyv hozzáadása/frissítése
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id = $_POST['id'] ?? '';
    $title = trim($_POST['title'] ?? '');
    $author = trim($_POST['author'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $stock = intval($_POST['stock'] ?? 0);
    $category = trim($_POST['category'] ?? '');
    $image = trim($_POST['image'] ?? '');
    $isbn = trim($_POST['isbn'] ?? '');
    $publisher = trim($_POST['publisher'] ?? '');
    $published_year = intval($_POST['published_year'] ?? '');
    $pages = intval($_POST['pages'] ?? '');
    
    try {
        if (empty($id)) {
            // Új könyv
            $stmt = $db->prepare("
                INSERT INTO books (title, author, description, price, stock, category, image, isbn, publisher, published_year, pages) 
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
            ");
            $stmt->execute([$title, $author, $description, $price, $stock, $category, $image, $isbn, $publisher, $published_year, $pages]);
            $message = 'Könyv sikeresen hozzáadva!';
        } else {
            // Könyv frissítése
            $stmt = $db->prepare("
                UPDATE books SET title=?, author=?, description=?, price=?, stock=?, category=?, image=?, isbn=?, publisher=?, published_year=?, pages=?
                WHERE id=?
            ");
            $stmt->execute([$title, $author, $description, $price, $stock, $category, $image, $isbn, $publisher, $published_year, $pages, $id]);
            $message = 'Könyv sikeresen frissítve!';
        }
    } catch (PDOException $e) {
        $message = 'Hiba: ' . $e->getMessage();
    }
}

// Könyv törlése
if (isset($_GET['delete'])) {
    $delete_id = $_GET['delete'];
    $stmt = $db->prepare("DELETE FROM books WHERE id = ?");
    $stmt->execute([$delete_id]);
    $message = 'Könyv sikeresen törölve!';
}

// Könyv szerkesztése
$edit_book = null;
if (isset($_GET['edit'])) {
    $edit_id = $_GET['edit'];
    $stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
    $stmt->execute([$edit_id]);
    $edit_book = $stmt->fetch(PDO::FETCH_ASSOC);
}

// Könyvek listázása
$search = $_GET['search'] ?? '';
$query = "SELECT * FROM books WHERE 1=1";
$params = [];

if (!empty($search)) {
    $query .= " AND (title LIKE ? OR author LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
}

$query .= " ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

$categories = ['Fiction', 'Science', 'History', 'Biography', 'Children', 'Technology', 'Art', 'Business'];
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
                        <a class="nav-link active" href="books.php">
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
                <h1 class="h2">Könyvek kezelése</h1>
                <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#bookModal">
                    <i class="bi bi-plus-circle"></i> Új könyv
                </button>
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
                                       placeholder="Keresés cím vagy szerző szerint..." 
                                       value="<?php echo htmlspecialchars($search); ?>">
                            </div>
                            <div class="col-md-4">
                                <button type="submit" class="btn btn-primary me-2">Keresés</button>
                                <a href="books.php" class="btn btn-outline-secondary">Összes</a>
                            </div>
                        </div>
                    </form>
                </div>
            </div>

            <!-- Könyvek táblázata -->
            <div class="card">
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped">
                            <thead>
                                <tr>
                                    <th>Kép</th>
                                    <th>Cím</th>
                                    <th>Szerző</th>
                                    <th>Kategória</th>
                                    <th>Ár</th>
                                    <th>Készlet</th>
                                    <th>Műveletek</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (empty($books)): ?>
                                    <tr>
                                        <td colspan="7" class="text-center text-muted py-4">
                                            Nincsenek könyvek
                                        </td>
                                    </tr>
                                <?php else: ?>
                                    <?php foreach($books as $book): ?>
                                    <tr>
                                        <td>
                                            <img src="<?php echo $book['image']; ?>" 
                                                 alt="<?php echo htmlspecialchars($book['title']); ?>" 
                                                 style="width: 50px; height: 70px; object-fit: cover;">
                                        </td>
                                        <td>
                                            <strong><?php echo htmlspecialchars($book['title']); ?></strong>
                                        </td>
                                        <td><?php echo htmlspecialchars($book['author']); ?></td>
                                        <td>
                                            <span class="badge bg-secondary"><?php echo $book['category']; ?></span>
                                        </td>
                                        <td>
                                            <strong><?php echo number_format($book['price'], 0, ',', ' '); ?> Ft</strong>
                                        </td>
                                        <td>
                                            <span class="badge <?php echo $book['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                                <?php echo $book['stock']; ?> db
                                            </span>
                                        </td>
                                        <td>
                                            <a href="?edit=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-outline-primary me-1">
                                                <i class="bi bi-pencil"></i>
                                            </a>
                                            <a href="?delete=<?php echo $book['id']; ?>" 
                                               class="btn btn-sm btn-outline-danger"
                                               onclick="return confirm('Biztosan törölni szeretnéd ezt a könyvet?')">
                                                <i class="bi bi-trash"></i>
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

<!-- Könyv modal -->
<div class="modal fade" id="bookModal" tabindex="-1">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">
                    <?php echo $edit_book ? 'Könyv szerkesztése' : 'Új könyv hozzáadása'; ?>
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form method="POST">
                <div class="modal-body">
                    <input type="hidden" name="id" value="<?php echo $edit_book['id'] ?? ''; ?>">
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Cím *</label>
                            <input type="text" class="form-control" name="title" 
                                   value="<?php echo htmlspecialchars($edit_book['title'] ?? ''); ?>" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Szerző *</label>
                            <input type="text" class="form-control" name="author" 
                                   value="<?php echo htmlspecialchars($edit_book['author'] ?? ''); ?>" required>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Leírás</label>
                        <textarea class="form-control" name="description" rows="3"><?php echo htmlspecialchars($edit_book['description'] ?? ''); ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Ár (Ft) *</label>
                            <input type="number" class="form-control" name="price" 
                                   value="<?php echo $edit_book['price'] ?? ''; ?>" step="0.01" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Készlet *</label>
                            <input type="number" class="form-control" name="stock" 
                                   value="<?php echo $edit_book['stock'] ?? 0; ?>" required>
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kategória *</label>
                            <select class="form-select" name="category" required>
                                <option value="">Válassz kategóriát...</option>
                                <?php foreach($categories as $cat): ?>
                                    <option value="<?php echo $cat; ?>" 
                                        <?php echo ($edit_book['category'] ?? '') === $cat ? 'selected' : ''; ?>>
                                        <?php echo $cat; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Kép URL</label>
                        <input type="url" class="form-control" name="image" 
                               value="<?php echo htmlspecialchars($edit_book['image'] ?? ''); ?>">
                    </div>
                    
                    <div class="row">
                        <div class="col-md-4 mb-3">
                            <label class="form-label">ISBN</label>
                            <input type="text" class="form-control" name="isbn" 
                                   value="<?php echo htmlspecialchars($edit_book['isbn'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kiadó</label>
                            <input type="text" class="form-control" name="publisher" 
                                   value="<?php echo htmlspecialchars($edit_book['publisher'] ?? ''); ?>">
                        </div>
                        <div class="col-md-4 mb-3">
                            <label class="form-label">Kiadás éve</label>
                            <input type="number" class="form-control" name="published_year" 
                                   value="<?php echo $edit_book['published_year'] ?? ''; ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label class="form-label">Oldalszám</label>
                        <input type="number" class="form-control" name="pages" 
                               value="<?php echo $edit_book['pages'] ?? ''; ?>">
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Mégse</button>
                    <button type="submit" class="btn btn-primary">
                        <?php echo $edit_book ? 'Frissítés' : 'Hozzáadás'; ?>
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<?php 
$no_footer = true;
include __DIR__ . '/../includes/footer.php'; 
?>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>

<?php if ($edit_book): ?>
<script>
    // Automatikus modal megnyitás szerkesztéskor
    document.addEventListener('DOMContentLoaded', function() {
        var bookModal = new bootstrap.Modal(document.getElementById('bookModal'));
        bookModal.show();
    });
</script>
<?php endif; ?> 
</body>
</html>