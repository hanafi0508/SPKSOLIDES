<?php require_once __DIR__ . '/../config/config.php'; ?>
<div class="col-md-2 text-white p-3 app-sidebar">

    <ul class="nav flex-column">

        <li class="sidebar-section">Utama</li>
        <li class="nav-item mb-2">
            <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/dashboard.php">
                <span class="nav-mark">D</span>
                Dashboard
            </a>
        </li>

        <?php if ($_SESSION['level'] == 'admin'): ?>

            <li class="sidebar-section">Data Master</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/kriteria/index.php">
                    <span class="nav-mark">K</span>
                    Kriteria
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white d-flex justify-content-between align-items-center" data-bs-toggle="collapse" href="#supplierMenu" role="button" aria-expanded="false" aria-controls="supplierMenu">
                    <span><span class="nav-mark">S</span> Supplier</span>
                    <span>+</span>
                </a>
            </li>
            <li class="nav-item">
                <div class="collapse" id="supplierMenu">
                    <div class="ps-3">
                        <a class="nav-link text-white-50" href="<?php echo BASE_URL; ?>/pages/supplier/index.php?tipe=barang">
                            <span class="nav-mark nav-mark-sub">B</span>
                            Barang
                        </a>
                        <a class="nav-link text-white-50" href="<?php echo BASE_URL; ?>/pages/supplier/index.php?tipe=jasa">
                            <span class="nav-mark nav-mark-sub">J</span>
                            Jasa
                        </a>
                    </div>
                </div>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/proyek/index.php">
                    <span class="nav-mark">P</span>
                    Proyek
                </a>
            </li>

            <li class="sidebar-section">Keputusan</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/alternatif/index.php">
                    <span class="nav-mark">A</span>
                    Perhitungan
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/penilaian/index.php">
                    <span class="nav-mark">N</span>
                    Penilaian
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ahp/input_perbandingan.php">
                    <span class="nav-mark">H</span>
                    AHP
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php">
                    <span class="nav-mark">R</span>
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    <span class="nav-mark">L</span>
                    Laporan
                </a>
            </li>

        <?php endif; ?>

        <?php if ($_SESSION['level'] == 'pimpinan'): ?>

            <li class="sidebar-section">Keputusan</li>

            <li class="nav-item mt-2">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/ranking/index.php">
                    <span class="nav-mark">R</span>
                    Ranking
                </a>
            </li>

            <li class="nav-item">
                <a class="nav-link text-white" href="<?php echo BASE_URL; ?>/pages/laporan/index.php">
                    <span class="nav-mark">L</span>
                    Laporan
                </a>
            </li>

        <?php endif; ?>

    </ul>

</div>
