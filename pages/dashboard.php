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

    <h3>Dashboard</h3>

    <div class="row">

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Proyek</h5>
                <h3><?= $total_proyek; ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Supplier</h5>
                <h3><?= $total_supplier; ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Kriteria</h5>
                <h3><?= $total_kriteria; ?></h3>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card p-3 text-center">
                <h5>Alternatif</h5>
                <h3><?= $total_alternatif; ?></h3>
            </div>
        </div>

    </div>

    <hr>

    <?php if ($_SESSION['level'] == 'admin'): ?>

        <h5>Menu Admin</h5>
        <a href="<?php echo BASE_URL; ?>/pages/kriteria/index.php" class="btn btn-primary">Kelola Kriteria</a>
        <a href="<?php echo BASE_URL; ?>/pages/supplier/index.php" class="btn btn-success">Kelola Supplier</a>
        <a href="<?php echo BASE_URL; ?>/pages/proyek/index.php" class="btn btn-warning">Kelola Proyek</a>

    <?php elseif ($_SESSION['level'] == 'pimpinan'): ?>

        <h5>Menu Pimpinan</h5>
        <a href="<?php echo BASE_URL; ?>/pages/ranking/index.php?metode=AHP" class="btn btn-info">Lihat Ranking</a>
        <a href="<?php echo BASE_URL; ?>/pages/laporan/index.php" class="btn btn-secondary">Lihat Laporan</a>

    <?php endif; ?>

</div>

<?php include '../layouts/footer.php'; ?>
