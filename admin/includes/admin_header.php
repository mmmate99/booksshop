<?php
// Admin header külön fájlba, hogy minden admin oldal használhassa
require_once __DIR__ . '/../../includes/config.php';
require_once __DIR__ . '/../../includes/auth.php';

if (!isAdmin()) {
    header('Location: ../../index.php');
    exit;
}

// Aktív oldal meghatározása
$current_page = basename($_SERVER['PHP_SELF']);
?>
<!DOCTYPE html>
<html lang="hu">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Panel - <?php echo APP_NAME; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link href="<?php echo APP_URL; ?>/assets/css/style.css" rel="stylesheet">
    <style>
        .sidebar {
            min-height: calc(100vh - 56px);
            box-shadow: inset -1px 0 0 rgba(0, 0, 0, .1);
        }
        
        .sidebar .nav-link {
            color: #333;
            padding: 0.75rem 1rem;
        }
        
        .sidebar .nav-link.active {
            color: #0d6efd;
            background-color: #e7f1ff;
        }
        
        .sidebar .nav-link:hover {
            color: #0d6efd;
            background-color: #f8f9fa;
        }
        
        .border-left-primary {
            border-left: 0.25rem solid #4e73df !important;
        }
        
        .border-left-success {
            border-left: 0.25rem solid #1cc88a !important;
        }
        
        .border-left-info {
            border-left: 0.25rem solid #36b9cc !important;
        }
        
        .border-left-warning {
            border-left: 0.25rem solid #f6c23e !important;
        }
        
        .text-xs {
            font-size: 0.7rem;
        }
        
        .text-gray-800 {
            color: #5a5c69 !important;
        }
        
        .text-gray-300 {
            color: #dddfeb !important;
        }
    </style>
</head>
<body>
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container-fluid">
            <a class="navbar-brand" href="<?php echo APP_URL; ?>/index.php">
                <i class="bi bi-book-half"></i> <?php echo APP_NAME; ?>
            </a>
            
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar">
                <span class="navbar-toggler-icon"></span>
            </button>
            
            <div class="collapse navbar-collapse" id="adminNavbar">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item">
                        <a class="nav-link" href="<?php echo APP_URL; ?>/index.php">
                            <i class="bi bi-house me-1"></i> Főoldal
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?php echo $current_page === 'index.php' ? 'active' : ''; ?>" href="index.php">
                            <i class="bi bi-speedometer2 me-1"></i> Admin Panel
                        </a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-gear me-1"></i> Kezelés
                        </a>
                        <ul class="dropdown-menu">
                            <li><a class="dropdown-item <?php echo $current_page === 'books.php' ? 'active' : ''; ?>" href="books.php"><i class="bi bi-book me-2"></i>Könyvek</a></li>
                            <li><a class="dropdown-item <?php echo $current_page === 'orders.php' ? 'active' : ''; ?>" href="orders.php"><i class="bi bi-receipt me-2"></i>Rendelések</a></li>
                            <li><a class="dropdown-item <?php echo $current_page === 'users.php' ? 'active' : ''; ?>" href="users.php"><i class="bi bi-people me-2"></i>Felhasználók</a></li>
                        </ul>
                    </li>
                </ul>
                
                <ul class="navbar-nav">
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown">
                            <i class="bi bi-person-circle me-1"></i> <?php echo $_SESSION['user_name']; ?>
                        </a>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/profile.php"><i class="bi bi-person me-2"></i>Profilom</a></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/orders.php"><i class="bi bi-receipt me-2"></i>Rendeléseim</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item" href="<?php echo APP_URL; ?>/index.php"><i class="bi bi-house me-2"></i>Vissza a boltba</a></li>
                            <li><hr class="dropdown-divider"></li>
                            <li><a class="dropdown-item text-danger" href="<?php echo APP_URL; ?>/logout.php"><i class="bi bi-box-arrow-right me-2"></i>Kijelentkezés</a></li>
                        </ul>
                    </li>
                </ul>
            </div>
        </div>
    </nav>