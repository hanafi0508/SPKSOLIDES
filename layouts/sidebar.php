<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="col-md-2 bg-dark text-white vh-100 p-3">

    <ul class="nav flex-column">

        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/dashboard.php">
                Dashboard
            </a>
        </li>

        <?php if ($_SESSION['level'] == 'admin'): ?>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/kriteria/index.php">
                    Kriteria
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/supplier/index.php">
                    Supplier
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/proyek/index.php">
                    Proyek
                </a>
            </li>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/alternatif/index.php">
                    Alternatif
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/penilaian/index.php">
                    Penilaian
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ahp/input_perbandingan.php">
                    AHP
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/fahp/input_perbandingan.php">
                    F-AHP
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php?metode=AHP">
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    Laporan
                </a>
            </li>

        <?php endif; ?>

        <?php if ($_SESSION['level'] == 'pimpinan'): ?>

            <hr>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php?metode=AHP">
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    Laporan
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>
