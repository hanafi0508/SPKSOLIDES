<?php require_once __DIR__ . '/../config/config.php'; ?>
<nav class="navbar navbar-dark app-navbar fixed-top shadow-sm">
    <div class="container-fluid">

        <div class="navbar-brand d-flex align-items-center gap-3 mb-0">
            <span class="brand-logo">SPK</span>
            <span class="brand-copy">
                <strong class="d-block">SPK Supplier</strong>
            </span>
        </div>

        <div class="d-flex align-items-center gap-2">
            <span class="user-pill">
                <span class="user-initial"><?= strtoupper(substr($_SESSION['nama_user'] ?? 'U', 0, 1)); ?></span>
                <span class="d-none d-sm-inline"><?= htmlspecialchars($_SESSION['nama_user']); ?></span>
            </span>

            <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-light btn-sm app-logout">
                Logout
            </a>
        </div>

    </div>
</nav>
