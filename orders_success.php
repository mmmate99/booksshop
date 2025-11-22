<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

if (!isLoggedIn()) {
    redirect('login.php');
}

if (!isset($_SESSION['order_success'])) {
    redirect('index.php');
}

$order_number = $_SESSION['order_success'];
unset($_SESSION['order_success']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rendelés sikeres - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8 text-center">
                <div class="card border-success">
                    <div class="card-body py-5">
                        <i class="bi bi-check-circle-fill text-success display-1"></i>
                        <h1 class="mt-4 text-success">Köszönjük a rendelést!</h1>
                        <p class="lead mt-3">A rendelésedet sikeresen fogadtuk.</p>
                        
                        <div class="alert alert-info mt-4">
                            <h5>Rendelés száma: <strong><?php echo $order_number; ?></strong></h5>
                            <p class="mb-0">Emailben elküldtük a rendelés részleteit.</p>
                        </div>
                        
                        <div class="mt-5">
                            <a href="orders.php" class="btn btn-primary me-3">Rendeléseim megtekintése</a>
                            <a href="index.php" class="btn btn-outline-secondary">Vásárlás folytatása</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>