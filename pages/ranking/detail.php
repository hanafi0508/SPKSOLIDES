<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;
$error = '';
$hasilRanking = null;

if ($id_proyek > 0) {
    try {
        $hasilRanking = hitungRankingAhp($conn, $id_proyek);
    } catch (Throwable $th) {
        $error = $th->getMessage();
    }
} else {
    $error = 'Proyek tidak valid.';
}

include '../../layouts/header.php';
include '../../layouts/navbar.php';
include '../../layouts/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3>Detail Ranking AHP</h3>

    <?php if ($error !== ''): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error); ?></div>
    <?php elseif ($hasilRanking !== null): ?>
        <div class="mb-3">
            <a href="index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-secondary">Kembali ke Ranking</a>
            <a href="../laporan/index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-dark">Laporan</a>
        </div>

        <div class="card">
            <div class="card-header bg-info text-white">Detail Perhitungan AHP</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark text-center">
                            <tr>
                                <th>Supplier</th>
                                <?php foreach ($hasilRanking['bobot'] as $bobot): ?>
                                    <th><?= htmlspecialchars($bobot['nama_kriteria']); ?></th>
                                <?php endforeach; ?>
                                <th>Nilai Akhir</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($hasilRanking['ranking'] as $idAlternatif => $rank): ?>
                                <tr>
                                    <td><?= htmlspecialchars($rank['supplier']); ?></td>
                                    <?php foreach ($hasilRanking['bobot'] as $idKriteria => $bobot): ?>
                                        <?php $detail = $hasilRanking['detail'][$idAlternatif][$idKriteria]; ?>
                                        <td>
                                            Nilai: <?= number_format($detail['nilai_asli'], 4); ?><br>
                                            Normalisasi: <?= number_format($detail['normalisasi'], 4); ?><br>
                                            Bobot: <?= number_format($detail['bobot'], 4); ?><br>
                                            Skor: <?= number_format($detail['skor'], 4); ?>
                                        </td>
                                    <?php endforeach; ?>
                                    <td class="text-center"><?= number_format($rank['nilai'], 6); ?></td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../layouts/footer.php'; ?>
