<?php require_once __DIR__ . '/../config/config.php'; ?>
<nav class="navbar navbar-dark app-navbar fixed-top shadow-sm">
    <div class="container-fluid">

        <div class="navbar-brand d-flex align-items-center gap-3 mb-0">
            <span class="brand-logo">SPK</span>
            <span class="brand-copy">
                <strong class="d-block">SPK Supplier</strong>
            </span>
        </div>

        <div class="d-flex">
            <span class="text-white me-3">
                <?= htmlspecialchars($_SESSION['nama_user']); ?>
            </span>

            <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-danger btn-sm">
                Logout
            </a>
        </div>

    </div>
</nav>
