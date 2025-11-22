<?php if (!isset($no_footer)): ?>
<footer class="bg-dark text-white py-4 mt-5">
    <div class="container">
        <div class="row">
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3"><?php echo APP_NAME; ?></h5>
                <p class="mb-0">Az ország legnagyobb online könyvesbolta. Minőségi könyvek, gyors szállítás.</p>
            </div>
            <div class="col-md-4 mb-4 mb-md-0">
                <h5 class="mb-3">Gyorslinkek</h5>
                <ul class="list-unstyled">
                    <li class="mb-2"><a href="index.php" class="text-white-50 text-decoration-none">Könyvek</a></li>
                    <li class="mb-2"><a href="about.php" class="text-white-50 text-decoration-none">Rólunk</a></li>
                    <li class="mb-2"><a href="contact.php" class="text-white-50 text-decoration-none">Kapcsolat</a></li>
                    <?php if (!isLoggedIn()): ?>
                        <li class="mb-2"><a href="login.php" class="text-white-50 text-decoration-none">Bejelentkezés</a></li>
                        <li class="mb-2"><a href="register.php" class="text-white-50 text-decoration-none">Regisztráció</a></li>
                    <?php else: ?>
                        <li class="mb-2"><a href="profile.php" class="text-white-50 text-decoration-none">Profilom</a></li>
                        <li class="mb-2"><a href="orders.php" class="text-white-50 text-decoration-none">Rendeléseim</a></li>
                    <?php endif; ?>
                </ul>
            </div>
            <div class="col-md-4">
                <h5 class="mb-3">Kapcsolat</h5>
                <p class="mb-2"><i class="bi bi-envelope me-2"></i> info@konyvesbolt.hu</p>
                <p class="mb-2"><i class="bi bi-telephone me-2"></i> +36 1 234 5678</p>
                <p class="mb-0"><i class="bi bi-geo-alt me-2"></i> 1061 Budapest, Andrássy út 1.</p>
            </div>
        </div>
        <hr class="my-4 bg-light">
        <div class="row align-items-center">
            <div class="col-md-6 text-center text-md-start">
                <p class="mb-0">&copy; 2024 <?php echo APP_NAME; ?>. Minden jog fenntartva.</p>
            </div>
            <div class="col-md-6 text-center text-md-end">
                <div class="d-flex justify-content-center justify-content-md-end">
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-facebook"></i></a>
                    <a href="#" class="text-white-50 me-3"><i class="bi bi-instagram"></i></a>
                    <a href="#" class="text-white-50"><i class="bi bi-twitter"></i></a>
                </div>
            </div>
        </div>
    </div>
</footer>

<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
<script src="assets/js/app.js"></script>
<?php endif; ?>