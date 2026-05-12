<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/fahp_function.php';

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
    FROM bobot_fahp b
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

$kriteria = [];
$queryKriteria = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY id_kriteria ASC");
while ($row = mysqli_fetch_assoc($queryKriteria)) {
    $kriteria[] = $row;
}

$perbandingan = [];
$stmtPerbandingan = mysqli_prepare($conn, "
    SELECT id_kriteria_1, id_kriteria_2, nilai_l, nilai_m, nilai_u
    FROM perbandingan_fahp
    WHERE id_proyek = ?
");
mysqli_stmt_bind_param($stmtPerbandingan, "i", $id_proyek);
mysqli_stmt_execute($stmtPerbandingan);
$resultPerbandingan = mysqli_stmt_get_result($stmtPerbandingan);

while ($row = mysqli_fetch_assoc($resultPerbandingan)) {
    $perbandingan[$row['id_kriteria_1']][$row['id_kriteria_2']] = [
        'l' => (float) $row['nilai_l'],
        'm' => (float) $row['nilai_m'],
        'u' => (float) $row['nilai_u'],
    ];
}

$detailHasil = null;
if (count($kriteria) >= 2 && !empty($perbandingan)) {
    $detailHasil = hitungFAHP($kriteria, $perbandingan);
}

include "../../layouts/header.php";
include "../../layouts/navbar.php";
include "../../layouts/sidebar.php";
?>

<div class="col-md-10 p-4">

    <div class="row mb-3">
        <div class="col-md-12">
            <h4>Hasil Perhitungan F-AHP</h4>
            <nav aria-label="breadcrumb">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item">F-AHP</li>
                    <li class="breadcrumb-item active">Hasil</li>
                </ol>
            </nav>
        </div>
    </div>

    <?php if (count($bobot) == 0) : ?>

        <div class="alert alert-warning">
            Data hasil F-AHP untuk proyek ini belum tersedia.
        </div>

        <?php if ($_SESSION['level'] === 'admin') : ?>
            <a href="input_perbandingan.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-primary">
                Input Perbandingan F-AHP
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
                        <th>Metode</th>
                        <td><span class="badge bg-info text-dark">F-AHP</span></td>
                    </tr>
                </table>
            </div>
        </div>

        <div class="alert alert-success">
            Perhitungan F-AHP berhasil dilakukan. Bobot yang ditampilkan adalah bobot normalisasi F-AHP.
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">
                Bobot Normalisasi Kriteria F-AHP
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
                                <th>Bobot F-AHP</th>
                                <th>Persentase</th>
                            </tr>
                        </thead>

                        <tbody>
                            <?php $no = 1; ?>
                            <?php foreach ($bobot as $b) : ?>
                                <tr>
                                    <td class="text-center"><?= $no++; ?></td>

                                    <td class="text-center">
                                        <?= htmlspecialchars($b['kode_kriteria']); ?>
                                    </td>

                                    <td>
                                        <?= htmlspecialchars($b['nama_kriteria']); ?>
                                    </td>

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
                    <?php endif; ?>

                    <a href="../dashboard.php" class="btn btn-secondary">
                        Kembali
                    </a>
                </div>
            </div>
        </div>

        <?php if ($detailHasil !== null) : ?>
            <div class="card mt-3">
                <div class="card-header bg-dark text-white">
                    Detail Perhitungan F-AHP
                </div>

                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle">
                            <thead class="table-dark text-center">
                                <tr>
                                    <th>Kriteria</th>
                                    <th>Jumlah Fuzzy</th>
                                    <th>Nilai Sintesis</th>
                                    <th>Minimum Derajat</th>
                                    <th>Bobot Normalisasi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($kriteria as $k) : ?>
                                    <?php $id = (int) $k['id_kriteria']; ?>
                                    <tr>
                                        <td>
                                            <?= htmlspecialchars($k['kode_kriteria']); ?> -
                                            <?= htmlspecialchars($k['nama_kriteria']); ?>
                                        </td>
                                        <td class="text-center">
                                            (<?= number_format($detailHasil['row_sum'][$id]['l'], 4); ?>,
                                            <?= number_format($detailHasil['row_sum'][$id]['m'], 4); ?>,
                                            <?= number_format($detailHasil['row_sum'][$id]['u'], 4); ?>)
                                        </td>
                                        <td class="text-center">
                                            (<?= number_format($detailHasil['synthetic'][$id]['l'], 4); ?>,
                                            <?= number_format($detailHasil['synthetic'][$id]['m'], 4); ?>,
                                            <?= number_format($detailHasil['synthetic'][$id]['u'], 4); ?>)
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($detailHasil['minimum'][$id], 6); ?>
                                        </td>
                                        <td class="text-center">
                                            <?= number_format($detailHasil['bobot'][$id], 6); ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                            <tfoot>
                                <tr>
                                    <th>Total Fuzzy</th>
                                    <th class="text-center">
                                        (<?= number_format($detailHasil['total_fuzzy']['l'], 4); ?>,
                                        <?= number_format($detailHasil['total_fuzzy']['m'], 4); ?>,
                                        <?= number_format($detailHasil['total_fuzzy']['u'], 4); ?>)
                                    </th>
                                    <th class="text-center">
                                        (<?= number_format($detailHasil['invers_total']['l'], 4); ?>,
                                        <?= number_format($detailHasil['invers_total']['m'], 4); ?>,
                                        <?= number_format($detailHasil['invers_total']['u'], 4); ?>)
                                    </th>
                                    <th colspan="2"></th>
                                </tr>
                            </tfoot>
                        </table>
                    </div>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>

</div>

<?php include "../../layouts/footer.php"; ?>
