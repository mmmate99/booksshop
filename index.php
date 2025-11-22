<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

// Könyvek lekérése
$db = getDB();
$search = $_GET['search'] ?? '';
$category = $_GET['category'] ?? '';

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

$query .= " ORDER BY title ASC";
$stmt = $db->prepare($query);
$stmt->execute($params);
$books = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Kategóriák
$categories = ['Fiction', 'Science', 'History', 'Biography', 'Children'];
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="assets/css/style.css" rel="stylesheet">
</head>
<body>
    <!-- Navigáció -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="index.php">
                <i class="bi bi-book-half"></i> <?php echo APP_NAME; ?>
            </a>
            
            <div class="navbar-nav ms-auto">
                <a class="nav-link" href="index.php">Könyvek</a>
                <a class="nav-link" href="cart.php">Kosár</a>
                
                <?php if (isLoggedIn()): ?>
                    <div class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item" href="profile.php">Profilom</a></li>
                            <li><a class="dropdown-item" href="orders.php">Rendeléseim</a></li>
                            <?php if (isAdmin()): ?>
                                <li><a class="dropdown-item" href="admin/">Admin Panel</a></li>
                            <?php endif; ?>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="logout.php">Kijelentkezés</a></li>
                        </ul>
                    </div>
                <?php else: ?>
                    <a class="nav-link" href="login.php">Bejelentkezés</a>
                    <a class="nav-link" href="register.php">Regisztráció</a>
                <?php endif; ?>
            </div>
        </div>
    </nav>

    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container text-center text-white py-5">
            <h1 class="display-4 fw-bold">Fedezd fel álmaid könyveit</h1>
            <p class="lead">Több ezer könyv egy helyen, kedvező áron</p>
        </div>
    </div>

    <!-- Tartalom -->
    <div class="container my-4">
        <!-- Keresés és szűrés -->
        <div class="row mb-4">
            <div class="col-md-8">
                <form method="GET" class="d-flex">
                    <input type="text" name="search" class="form-control me-2" 
                           placeholder="Keresés..." value="<?php echo htmlspecialchars($search); ?>">
                    <select name="category" class="form-select me-2">
                        <option value="">Összes kategória</option>
                        <?php foreach($categories as $cat): ?>
                            <option value="<?php echo $cat; ?>" <?php echo $category === $cat ? 'selected' : ''; ?>>
                                <?php echo $cat; ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <button type="submit" class="btn btn-primary">Keresés</button>
                </form>
            </div>
        </div>

        <!-- Könyvek grid -->
        <div class="row">
            <?php if (empty($books)): ?>
                <div class="col-12 text-center py-5">
                    <i class="bi bi-search display-1 text-muted"></i>
                    <h3 class="mt-3">Nincs találat</h3>
                    <p>Próbálj meg másik keresési kifejezést!</p>
                </div>
            <?php else: ?>
                <?php foreach($books as $book): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card book-card h-100">
                            <img src="<?php echo $book['image']; ?>" class="card-img-top book-image" 
                                 alt="<?php echo htmlspecialchars($book['title']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($book['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($book['author']); ?></p>
                                <span class="badge bg-secondary mb-2"><?php echo $book['category']; ?></span>
                                <p class="card-text flex-grow-1 small">
                                    <?php echo mb_substr($book['description'], 0, 100) . '...'; ?>
                                </p>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0"><?php echo number_format($book['price'], 0, ',', ' '); ?> Ft</span>
                                        <span class="badge <?php echo $book['stock'] > 0 ? 'bg-success' : 'bg-danger'; ?>">
                                            <?php echo $book['stock'] > 0 ? $book['stock'] . ' db' : 'Elfogyott'; ?>
                                        </span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <?php if (isLoggedIn() && $book['stock'] > 0): ?>
                                            <form method="POST" action="api/cart_add.php">
                                                <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                                                <button type="submit" class="btn btn-primary w-100">
                                                    <i class="bi bi-cart-plus"></i> Kosárba
                                                </button>
                                            </form>
                                        <?php elseif (!isLoggedIn()): ?>
                                            <a href="login.php" class="btn btn-primary w-100">
                                                <i class="bi bi-cart-plus"></i> Kosárba
                                            </a>
                                        <?php else: ?>
                                            <button class="btn btn-secondary w-100" disabled>Elfogyott</button>
                                        <?php endif; ?>
                                        <a href="book.php?id=<?php echo $book['id']; ?>" class="btn btn-outline-secondary w-100">
                                            <i class="bi bi-info-circle"></i> Részletek
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
        </div>
    </div>

    <!-- Footer -->
    <footer class="bg-dark text-white py-4 mt-5">
        <div class="container text-center">
            <p>&copy; 2025 <?php echo APP_NAME; ?>. Minden jog fenntartva.</p>
        </div>
    </footer>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/app.js"></script>
</body>
</html>