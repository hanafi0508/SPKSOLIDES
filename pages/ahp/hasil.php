<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;

if ($id_proyek <= 0) {
    echo "<script>alert('Proyek tidak valid!'); window.location='input_perbandingan.php';</script>";
    exit;
}

$stmtProyek = mysqli_prepare($conn, "SELECT * FROM proyek WHERE id_proyek = ?");
mysqli_stmt_bind_param($stmtProyek, "i", $id_proyek);
mysqli_stmt_execute($stmtProyek);
$resultProyek = mysqli_stmt_get_result($stmtProyek);
$proyek = mysqli_fetch_assoc($resultProyek);

if (!$proyek) {
    echo "<script>alert('Data proyek tidak ditemukan!'); window.location='input_perbandingan.php';</script>";
    exit;
}

$stmtBobot = mysqli_prepare($conn, "
    SELECT 
        b.*,
        k.kode_kriteria,
        k.nama_kriteria,
        k.jenis_kriteria
    FROM bobot_ahp b
    JOIN kriteria k ON b.id_kriteria = k.id_kriteria
    WHERE b.id_proyek = ?
    ORDER BY k.id_kriteria ASC
");

mysqli_stmt_bind_param($stmtBobot, "i", $id_proyek);
mysqli_stmt_execute($stmtBobot);
$resultBobot = mysqli_stmt_get_result($stmtBobot);

$bobot = [];

while ($row = mysqli_fetch_assoc($resultBobot)) {
    $bobot[] = $row;
}

$lambda = $bobot[0]['lambda_value'] ?? 0;
$ci = $bobot[0]['ci'] ?? 0;
$cr = $bobot[0]['cr'] ?? 0;
$status = $bobot[0]['status_konsistensi'] ?? '-';

include "../../layouts/header.php";
include "../../layouts/navbar.php";
include "../../layouts/sidebar.php";
?>

<div class="col-md-10 p-4">

    <div class="row mb-3">
        <div class="col-md-12">
            <h4>Hasil Perhitungan AHP</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">AHP</li>
                    <li class="breadcrumb-item active">Hasil</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (count($bobot) == 0) : ?>

        <div class="alert alert-warning">
            Data hasil AHP untuk proyek ini belum tersedia.
        </div>

        <?php if ($_SESSION['level'] === 'admin') : ?>
            <a href="input_perbandingan.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-primary">
                Input Perbandingan AHP
            </a>
        <?php endif; ?>

        <a href="../dashboard.php" class="btn btn-secondary">
            Kembali
        </a>

    <?php else : ?>

        <div class="card mb-3">
            <div class="card-header bg-primary text-white">
                Informasi Proyek
            </div>
            <div class="card-body">
                <table class="table table-bordered">
                    <tr>
                        <th width="220">Nama Proyek</th>
                        <td><?= htmlspecialchars($proyek['nama_proyek']); ?></td>
                    </tr>
                    <tr>
                        <th>Lokasi</th>
                        <td><?= htmlspecialchars($proyek['lokasi'] ?? '-'); ?></td>
                    </tr>
                    <tr>
                        <th>Status Konsistensi</th>
                        <td>
                            <?php if ($status == 'konsisten') : ?>
                                <span class="badge bg-success">Konsisten</span>
                            <?php else : ?>
                                <span class="badge bg-danger">Tidak Konsisten</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="row mb-3">

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-dark text-white">
                        Lambda Maksimum
                    </div>
                    <div class="card-body">
                        <h5><?= number_format((float) $lambda, 6); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-dark text-white">
                        CI
                    </div>
                    <div class="card-body">
                        <h5><?= number_format((float) $ci, 6); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-dark text-white">
                        CR
                    </div>
                    <div class="card-body">
                        <h5><?= number_format((float) $cr, 6); ?></h5>
                    </div>
                </div>
            </div>

            <div class="col-md-3">
                <div class="card text-center">
                    <div class="card-header bg-dark text-white">
                        Status
                    </div>
                    <div class="card-body">
                        <?php if ($status == 'konsisten') : ?>
                            <span class="badge bg-success fs-6">Konsisten</span>
                        <?php else : ?>
                            <span class="badge bg-danger fs-6">Tidak Konsisten</span>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

        </div>

        <?php if ($status == 'tidak_konsisten') : ?>
            <div class="alert alert-danger">
                Nilai CR lebih dari 0,10. Bobot AHP belum disarankan untuk digunakan pada proses ranking.
                Silakan perbaiki nilai perbandingan kriteria.
            </div>
        <?php else : ?>
            <div class="alert alert-success">
                Nilai CR kurang dari atau sama dengan 0,10. Bobot AHP valid dan dapat digunakan untuk proses ranking.
            </div>
        <?php endif; ?>

        <div class="card">
            <div class="card-header bg-info text-white">
                Bobot Prioritas Kriteria AHP
            </div>

            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th width="60">No</th>
                                <th>Kode</th>
                                <th>Nama Kriteria</th>
                                <th>Jenis</th>
                                <th>Bobot</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($bobot as $b) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>
                                    <td class="text-center"><?= htmlspecialchars($b['kode_kriteria']); ?></td>
                                    <td><?= htmlspecialchars($b['nama_kriteria']); ?></td>
                                    <td class="text-center">
                                        <?php if ($b['jenis_kriteria'] == 'benefit') : ?>
                                            <span class="badge bg-success">Benefit</span>
                                        <?php else : ?>
                                            <span class="badge bg-warning text-dark">Cost</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format((float) $b['bobot'], 6); ?>
                                    </td>
                                    <td class="text-center">
                                        <?= number_format((float) $b['bobot'] * 100, 2); ?>%
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div class="mt-3">
                    <?php if ($_SESSION['level'] === 'admin') : ?>
                        <a href="input_perbandingan.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-warning">
                            Edit Perbandingan
                        </a>
                        <?php if ($status == 'konsisten') : ?>
                            <a href="../ranking/index.php?id_proyek=<?= $id_proyek; ?>&metode=AHP" class="btn btn-primary">
                                Lihat Ranking AHP
                            </a>
                        <?php endif; ?>
                    <?php endif; ?>

                    <a href="../dashboard.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </div>
        </div>

    <?php endif; ?>

</div>

<?php include "../../layouts/footer.php"; ?>
