<?php require_once __DIR__ . '/../config/config.php'; ?>
<nav class="navbar navbar-dark app-navbar fixed-top shadow-sm">
    <div class="container-fluid">

        <div class="navbar-brand d-flex align-items-center gap-3 mb-0">
            <img src="<?php echo BASE_URL; ?>/assets/logo-solides.png" alt="SOLIDES" class="brand-img">
            <span class="brand-copy">
                <strong class="d-block">SOLIDES</strong>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="user-pill">
                <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['nama_user']); ?></span>
            </span>

            <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-light btn-sm app-logout">
                Logout
            </a>
        </div>

    </div>
</nav>
