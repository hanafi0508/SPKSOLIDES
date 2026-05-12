<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;
$error = '';
$rankingAhp = [];
$rankingFahp = [];
$perbandingan = [];
$kesimpulan = '';
$rekomendasiAhp = null;
$rekomendasiFahp = null;

if ($id_proyek > 0) {
    try {
        $hasilAhp = hitungRankingAhp($conn, $id_proyek);
        simpanRankingAhp($conn, $id_proyek, $hasilAhp['ranking']);
        $rankingAhp = array_values($hasilAhp['ranking']);
    } catch (Throwable $th) {
        $error = 'AHP: ' . $th->getMessage();
    }

    try {
        $hasilFahp = hitungRankingFahp($conn, $id_proyek);
        simpanRankingFahp($conn, $id_proyek, $hasilFahp['ranking']);
        $rankingFahp = array_values($hasilFahp['ranking']);
    } catch (Throwable $th) {
        $error .= ($error !== '' ? ' ' : '') . 'F-AHP: ' . $th->getMessage();
    }

    if (!empty($rankingAhp) && !empty($rankingFahp)) {
        $perbandingan = buildPerbandinganRanking($rankingAhp, $rankingFahp);
        $kesimpulan = buildKesimpulanPerbandingan($rankingAhp, $rankingFahp);
        $rekomendasiAhp = $rankingAhp[0] ?? null;
        $rekomendasiFahp = $rankingFahp[0] ?? null;
    }
}

include '../../layouts/header.php';
include '../../layouts/navbar.php';
include '../../layouts/sidebar.php';
?>

<div class="col-md-10 p-4">
    <h3>Perbandingan Metode</h3>

    <div class="mb-3">
        <a href="index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-secondary">Kembali ke Ranking</a>
        <a href="../laporan/index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-dark">Laporan</a>
    </div>

    <?php if ($error !== ''): ?>
        <div class="alert alert-warning"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <?php if (!empty($perbandingan)): ?>
        <div class="card mb-3">
            <div class="card-header bg-warning text-dark">Kesimpulan Otomatis</div>
            <div class="card-body">
                <p class="mb-0"><?= htmlspecialchars($kesimpulan); ?></p>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-info text-white">Perbandingan Ranking</div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead class="table-dark">
                            <tr>
                                <th>Supplier</th>
                                <th width="120">Ranking AHP</th>
                                <th width="120">Ranking F-AHP</th>
                                <th width="120">Selisih</th>
                                <th width="140">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($perbandingan as $row): ?>
                                <tr>
                                    <td><?= htmlspecialchars($row['nama_supplier']); ?></td>
                                    <td class="text-center"><?= $row['ranking_ahp']; ?></td>
                                    <td class="text-center"><?= $row['ranking_fahp'] ?? '-'; ?></td>
                                    <td class="text-center"><?= $row['selisih'] ?? '-'; ?></td>
                                    <td class="text-center">
                                        <span class="badge bg-<?= $row['status'] === 'Tetap' ? 'success' : 'warning text-dark'; ?>">
                                            <?= htmlspecialchars($row['status']); ?>
                                        </span>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">Rekomendasi Akhir</div>
            <div class="card-body">
                <?php if ($rekomendasiAhp && $rekomendasiFahp && $rekomendasiAhp['supplier'] === $rekomendasiFahp['supplier']): ?>
                    <p class="mb-0">
                        Supplier akhir yang direkomendasikan adalah <strong><?= htmlspecialchars($rekomendasiAhp['supplier']); ?></strong>
                        karena konsisten berada di peringkat pertama pada kedua metode.
                    </p>
                <?php else: ?>
                    <table class="table table-bordered mb-0">
                        <thead class="table-dark">
                            <tr>
                                <th>Metode</th>
                                <th>Supplier</th>
                                <th width="180">Nilai</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td>AHP</td>
                                <td><?= $rekomendasiAhp ? htmlspecialchars($rekomendasiAhp['supplier']) : '-'; ?></td>
                                <td class="text-center"><?= $rekomendasiAhp ? number_format($rekomendasiAhp['nilai'], 6) : '-'; ?></td>
                            </tr>
                            <tr>
                                <td>F-AHP</td>
                                <td><?= $rekomendasiFahp ? htmlspecialchars($rekomendasiFahp['supplier']) : '-'; ?></td>
                                <td class="text-center"><?= $rekomendasiFahp ? number_format($rekomendasiFahp['nilai'], 6) : '-'; ?></td>
                            </tr>
                        </tbody>
                    </table>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php include '../../layouts/footer.php'; ?>
