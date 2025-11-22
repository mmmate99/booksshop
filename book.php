<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$book_id = $_GET['id'] ?? '';

if (empty($book_id)) {
    redirect('index.php');
}

$db = getDB();
$stmt = $db->prepare("SELECT * FROM books WHERE id = ?");
$stmt->execute([$book_id]);
$book = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$book) {
    redirect('index.php');
}

// Hasonló könyvek azonos kategóriából
$stmt = $db->prepare("SELECT * FROM books WHERE category = ? AND id != ? LIMIT 4");
$stmt->execute([$book['category'], $book_id]);
$similar_books = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($book['title']); ?> - <?php echo APP_NAME; ?></title>
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
                <li class="breadcrumb-item"><a href="index.php?category=<?php echo urlencode($book['category']); ?>"><?php echo $book['category']; ?></a></li>
                <li class="breadcrumb-item active"><?php echo htmlspecialchars($book['title']); ?></li>
            </ol>
        </nav>

        <div class="row">
            <div class="col-md-4">
                <img src="<?php echo $book['image']; ?>" 
                     alt="<?php echo htmlspecialchars($book['title']); ?>" 
                     class="img-fluid rounded shadow">
            </div>
            
            <div class="col-md-8">
                <h1 class="display-5 fw-bold"><?php echo htmlspecialchars($book['title']); ?></h1>
                <p class="lead text-muted"><?php echo htmlspecialchars($book['author']); ?></p>
                
                <div class="mb-3">
                    <span class="badge bg-primary fs-6"><?php echo $book['category']; ?></span>
                    <?php if ($book['stock'] > 0): ?>
                        <span class="badge bg-success fs-6"><?php echo $book['stock']; ?> db raktáron</span>
                    <?php else: ?>
                        <span class="badge bg-danger fs-6">Elfogyott</span>
                    <?php endif; ?>
                </div>

                <div class="mb-4">
                    <span class="display-6 text-primary fw-bold"><?php echo number_format($book['price'], 0, ',', ' '); ?> Ft</span>
                </div>

                <div class="mb-4">
                    <h4>Leírás</h4>
                    <p class="fs-5"><?php echo nl2br(htmlspecialchars($book['description'])); ?></p>
                </div>

                <?php if (!empty($book['isbn'])): ?>
                <div class="mb-3">
                    <strong>ISBN:</strong> <?php echo $book['isbn']; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($book['publisher'])): ?>
                <div class="mb-3">
                    <strong>Kiadó:</strong> <?php echo $book['publisher']; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($book['published_year'])): ?>
                <div class="mb-3">
                    <strong>Kiadás éve:</strong> <?php echo $book['published_year']; ?>
                </div>
                <?php endif; ?>

                <?php if (!empty($book['pages'])): ?>
                <div class="mb-4">
                    <strong>Oldalszám:</strong> <?php echo $book['pages']; ?>
                </div>
                <?php endif; ?>

                <div class="d-grid gap-2 d-md-flex">
                    <?php if (isLoggedIn() && $book['stock'] > 0): ?>
                        <form method="POST" action="api/cart_add.php" class="me-2 flex-grow-1">
                            <input type="hidden" name="book_id" value="<?php echo $book['id']; ?>">
                            <button type="submit" class="btn btn-primary btn-lg w-100">
                                <i class="bi bi-cart-plus"></i> Kosárba
                            </button>
                        </form>
                    <?php elseif (!isLoggedIn()): ?>
                        <a href="login.php" class="btn btn-primary btn-lg w-100">
                            <i class="bi bi-cart-plus"></i> Kosárba
                        </a>
                    <?php else: ?>
                        <button class="btn btn-secondary btn-lg w-100" disabled>Elfogyott</button>
                    <?php endif; ?>
                    
                    <button class="btn btn-outline-secondary btn-lg" onclick="history.back()">
                        <i class="bi bi-arrow-left"></i> Vissza
                    </button>
                </div>
            </div>
        </div>

        <!-- Hasonló könyvek -->
        <?php if (!empty($similar_books)): ?>
        <div class="row mt-5">
            <div class="col-12">
                <h3 class="mb-4">Hasonló könyvek</h3>
                <div class="row">
                    <?php foreach($similar_books as $similar): ?>
                    <div class="col-lg-3 col-md-4 col-sm-6 mb-4">
                        <div class="card book-card h-100">
                            <img src="<?php echo $similar['image']; ?>" class="card-img-top book-image" 
                                 alt="<?php echo htmlspecialchars($similar['title']); ?>">
                            <div class="card-body d-flex flex-column">
                                <h5 class="card-title"><?php echo htmlspecialchars($similar['title']); ?></h5>
                                <p class="card-text text-muted"><?php echo htmlspecialchars($similar['author']); ?></p>
                                <span class="badge bg-secondary mb-2"><?php echo $similar['category']; ?></span>
                                <div class="mt-auto">
                                    <div class="d-flex justify-content-between align-items-center mb-3">
                                        <span class="h5 text-primary mb-0"><?php echo number_format($similar['price'], 0, ',', ' '); ?> Ft</span>
                                    </div>
                                    <div class="d-grid gap-2">
                                        <a href="book.php?id=<?php echo $similar['id']; ?>" class="btn btn-outline-primary w-100">
                                            Részletek
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/app.js"></script>
</body>
</html>