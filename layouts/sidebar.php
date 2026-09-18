<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="col-md-2 text-white p-3 app-sidebar">

    <ul class="nav flex-column">

        <li class="sidebar-section">Utama</li>
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/dashboard.php">
                <span class="nav-mark"><i class="bi bi-grid-1x2-fill"></i></span>
                Dashboard
            </a>
        </li>

        <?php if ($_SESSION['level'] == 'admin'): ?>

            <li class="sidebar-section">Data Master</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/kriteria/index.php">
                    <span class="nav-mark"><i class="bi bi-list-check"></i></span>
                    Kriteria
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/supplier/index.php">
                    <span class="nav-mark"><i class="bi bi-truck"></i></span>
                    Supplier
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/proyek/index.php">
                    <span class="nav-mark"><i class="bi bi-briefcase"></i></span>
                    Proyek
                </a>
            </li>

            <li class="sidebar-section">Keputusan</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/alternatif/index.php">
                    <span class="nav-mark"><i class="bi bi-calculator"></i></span>
                    Perhitungan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/penilaian/index.php">
                    <span class="nav-mark"><i class="bi bi-clipboard-check"></i></span>
                    Penilaian
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ahp/input_perbandingan.php">
                    <span class="nav-mark"><i class="bi bi-diagram-3"></i></span>
                    AHP
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php">
                    <span class="nav-mark"><i class="bi bi-trophy"></i></span>
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    <span class="nav-mark"><i class="bi bi-file-earmark-text"></i></span>
                    Laporan
                </a>
            </li>

        <?php endif; ?>

        <?php if ($_SESSION['level'] == 'pimpinan'): ?>

            <li class="sidebar-section">Keputusan</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php">
                    <span class="nav-mark"><i class="bi bi-trophy"></i></span>
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    <span class="nav-mark"><i class="bi bi-file-earmark-text"></i></span>
                    Laporan
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>
