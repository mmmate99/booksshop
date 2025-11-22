<?php
require_once 'includes/config.php';
require_once 'includes/auth.php';

$errors = [];
$success = false;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name'] ?? '');
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validáció
    if (empty($name)) {
        $errors[] = 'A név megadása kötelező!';
    }
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Érvényes email cím megadása kötelező!';
    }
    
    if (strlen($password) < 6) {
        $errors[] = 'A jelszónak legalább 6 karakter hosszúnak kell lennie!';
    }
    
    if ($password !== $confirm_password) {
        $errors[] = 'A jelszavak nem egyeznek!';
    }
    
    if (empty($errors)) {
        if (register($name, $email, $password)) {
            $success = true;
            // Automatikus bejelentkezés
            login($email, $password);
        } else {
            $errors[] = 'Ez az email cím már regisztrálva van!';
        }
    }
}

if (isLoggedIn() && !$success) {
    redirect('index.php');
}
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Regisztráció - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>

    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6 col-lg-5">
                <div class="card shadow">
                    <div class="card-body p-4">
                        <h2 class="text-center mb-4">Regisztráció</h2>
                        
                        <?php if ($success): ?>
                            <div class="alert alert-success">
                                Sikeres regisztráció! Átirányítás...
                            </div>
                            <script>
                                setTimeout(() => {
                                    window.location.href = 'index.php';
                                }, 2000);
                            </script>
                        <?php else: ?>
                            <?php if (!empty($errors)): ?>
                                <div class="alert alert-danger">
                                    <?php foreach($errors as $error): ?>
                                        <div><?php echo $error; ?></div>
                                    <?php endforeach; ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST">
                                <div class="mb-3">
                                    <label for="name" class="form-label">Teljes név</label>
                                    <input type="text" class="form-control" id="name" name="name" 
                                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">Email cím</label>
                                    <input type="email" class="form-control" id="email" name="email" 
                                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="password" class="form-label">Jelszó</label>
                                    <input type="password" class="form-control" id="password" name="password" required>
                                    <div class="form-text">A jelszónak legalább 6 karakter hosszúnak kell lennie.</div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Jelszó megerősítése</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                </div>
                                
                                <button type="submit" class="btn btn-primary w-100">Regisztráció</button>
                                
                                <div class="text-center mt-3">
                                    <a href="login.php" class="text-decoration-none">Már van fiókod? Jelentkezz be!</a>
                                </div>
                            </form>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <?php include 'includes/footer.php'; ?>
</body>
</html>