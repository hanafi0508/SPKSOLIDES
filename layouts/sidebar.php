<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="col-md-2 text-white p-3 app-sidebar d-flex flex-column">

    <div class="d-flex align-items-center gap-3 mb-3 sidebar-brand">
        <img src="<?php echo BASE_URL; ?>/assets/logo-solides.png" alt="SOLIDES" class="brand-logo-img">
        <span class="brand-copy">
            <strong class="d-block">SOLIDES</strong>
        </span>
    </div>

    <div class="sidebar-user-card mb-3">
        <div class="sidebar-user-meta">
            <small class="user-welcome">Selamat datang,</small>
            <strong class="user-name"><?= htmlspecialchars($_SESSION['nama_user']); ?></strong>
        </div>
    </div>

    <ul class="nav flex-column flex-grow-1">
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/dashboard.php">
                <i class="bi bi-speedometer2 nav-icon"></i>
                Dashboard
            </a>
        </li>

        <?php if ($_SESSION['level'] == 'admin'): ?>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/kriteria/index.php">
                    <i class="bi bi-list-check nav-icon"></i>
                    Kriteria
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/supplier/index.php">
                    <i class="bi bi-truck nav-icon"></i>
                    Supplier
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/proyek/index.php">
                    <i class="bi bi-briefcase nav-icon"></i>
                    Proyek
                </a>
            </li>
            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ahp/input_alternatif.php">
                    <i class="bi bi-person-check nav-icon"></i>
                    Penilaian Supplier
                </a>
            </li>

        <?php endif; ?>

    </ul>

    <div class="sidebar-user d-flex justify-content-center pt-3 mt-3">
        <a href="<?php echo BASE_URL; ?>/auth/logout.php" class="btn btn-outline-light btn-sm app-logout">
            <i class="bi bi-box-arrow-right me-1"></i>Logout
        </a>
    </div>

</div>