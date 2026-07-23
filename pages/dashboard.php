<?php
require_once '../config/session.php';
require_once '../config/database.php';
require_once '../functions/auth_function.php';

check_login();

$total_proyek = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM proyek"))['total'];
$total_supplier = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM supplier"))['total'];
$total_kriteria = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM kriteria"))['total'];
$total_alternatif = mysqli_fetch_assoc(mysqli_query($conn, "SELECT COUNT(*) as total FROM alternatif"))['total'];

include '../layouts/header.php';
include '../layouts/navbar.php';
include '../layouts/sidebar.php';
?>

<div class="col-md-10 p-4">

    <div class="page-toolbar">
        <div>
            <h3>Dashboard</h3>
        </div>
    </div>

    <div class="row g-3">

        <div class="col-md-3">
            <div class="card metric-card">
                <span class="metric-label">Proyek</span>
                <strong class="metric-value"><?= $total_proyek; ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card metric-card">
                <span class="metric-label">Supplier</span>
                <strong class="metric-value"><?= $total_supplier; ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card metric-card">
                <span class="metric-label">Kriteria</span>
                <strong class="metric-value"><?= $total_kriteria; ?></strong>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card metric-card">
                <span class="metric-label">Perhitungan</span>
                <strong class="metric-value"><?= $total_alternatif; ?></strong>
            </div>
        </div>

    </div>

    <div class="card mt-3">
        <div class="card-header bg-dark text-white">Akses Cepat</div>
        <div class="card-body">

    <?php if ($_SESSION['level'] == 'admin'): ?>

        <div class="d-flex flex-wrap gap-2 page-actions">
            <a href="<?php echo BASE_URL; ?>/pages/kriteria/index.php" class="btn btn-primary">Kelola Kriteria</a>
            <a href="<?php echo BASE_URL; ?>/pages/supplier/index.php" class="btn btn-primary">Kelola Supplier</a>
            <a href="<?php echo BASE_URL; ?>/pages/proyek/index.php" class="btn btn-warning">Kelola Proyek</a>
            <a href="<?php echo BASE_URL; ?>/pages/ahp/input_perbandingan.php" class="btn btn-dark">Input AHP</a>
        </div>

    <?php elseif ($_SESSION['level'] == 'pimpinan'): ?>

        <div class="d-flex flex-wrap gap-2 page-actions">
            <a href="<?php echo BASE_URL; ?>/pages/ranking/index.php" class="btn btn-primary">Lihat Ranking</a>
            <a href="<?php echo BASE_URL; ?>/pages/laporan/index.php" class="btn btn-secondary">Lihat Laporan</a>
        </div>

    <?php endif; ?>
        </div>
    </div>

</div>

<?php include '../layouts/footer.php'; ?>
