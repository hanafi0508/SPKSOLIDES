<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;
$errorAhp = '';
$errorFahp = '';
$hasilAhp = null;
$hasilFahp = null;
$perbandingan = [];
$kesimpulan = '';
$rekomendasiAhp = null;
$rekomendasiFahp = null;

$proyekList = mysqli_query($conn, "SELECT id_proyek, nama_proyek FROM proyek ORDER BY id_proyek DESC");

if ($id_proyek > 0) {
    try {
        $hasilAhp = hitungRankingAhp($conn, $id_proyek);
        simpanRankingAhp($conn, $id_proyek, $hasilAhp['ranking']);
    } catch (Throwable $th) {
        $errorAhp = $th->getMessage();
    }

    try {
        $hasilFahp = hitungRankingFahp($conn, $id_proyek);
        simpanRankingFahp($conn, $id_proyek, $hasilFahp['ranking']);
    } catch (Throwable $th) {
        $errorFahp = $th->getMessage();
    }

    if ($hasilAhp !== null && $hasilFahp !== null) {
        $rankingAhp = array_values($hasilAhp['ranking']);
        $rankingFahp = array_values($hasilFahp['ranking']);
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

    <h3>Halaman Hasil Ranking</h3>

    <form method="GET" class="row g-2 mb-3">
        <div class="col-md-8">
            <select name="id_proyek" class="form-select" required>
                <option value="">-- Pilih Proyek --</option>
                <?php while ($proyek = mysqli_fetch_assoc($proyekList)): ?>
                    <option value="<?= $proyek['id_proyek']; ?>" <?= $id_proyek === (int) $proyek['id_proyek'] ? 'selected' : ''; ?>>
                        <?= htmlspecialchars($proyek['nama_proyek']); ?>
                    </option>
                <?php endwhile; ?>
            </select>
        </div>
        <div class="col-md-4">
            <button type="submit" class="btn btn-primary">Tampilkan Ranking</button>
        </div>
    </form>

    <?php if ($id_proyek > 0): ?>
        <div class="mb-3">
            <a href="detail.php?id_proyek=<?= $id_proyek; ?>&metode=AHP" class="btn btn-outline-primary">Detail AHP</a>
            <a href="detail.php?id_proyek=<?= $id_proyek; ?>&metode=F-AHP" class="btn btn-outline-info">Detail F-AHP</a>
            <a href="perbandingan.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-warning">Perbandingan</a>
            <a href="../laporan/index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-dark">Laporan</a>
        </div>
    <?php endif; ?>

    <?php if ($errorAhp !== ''): ?>
        <div class="alert alert-warning">AHP: <?= htmlspecialchars($errorAhp); ?></div>
    <?php endif; ?>

    <?php if ($errorFahp !== ''): ?>
        <div class="alert alert-warning">F-AHP: <?= htmlspecialchars($errorFahp); ?></div>
    <?php endif; ?>

    <?php if ($hasilAhp !== null || $hasilFahp !== null): ?>
        <div class="row mb-3">
            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-primary text-white">Ranking AHP</div>
                    <div class="card-body">
                        <?php if ($hasilAhp === null): ?>
                            <div class="alert alert-warning mb-0">Ranking AHP belum tersedia.</div>
                        <?php else: ?>
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="80">Ranking</th>
                                        <th>Supplier</th>
                                        <th width="180">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hasilAhp['ranking'] as $row): ?>
                                        <tr>
                                            <td class="text-center"><?= $row['ranking']; ?></td>
                                            <td><?= htmlspecialchars($row['supplier']); ?></td>
                                            <td class="text-center"><?= number_format($row['nilai'], 6); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="col-md-6">
                <div class="card h-100">
                    <div class="card-header bg-info text-white">Ranking F-AHP</div>
                    <div class="card-body">
                        <?php if ($hasilFahp === null): ?>
                            <div class="alert alert-warning mb-0">Ranking F-AHP belum tersedia.</div>
                        <?php else: ?>
                            <table class="table table-bordered table-striped align-middle mb-0">
                                <thead class="table-dark">
                                    <tr>
                                        <th width="80">Ranking</th>
                                        <th>Supplier</th>
                                        <th width="180">Nilai</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($hasilFahp['ranking'] as $row): ?>
                                        <tr>
                                            <td class="text-center"><?= $row['ranking']; ?></td>
                                            <td><?= htmlspecialchars($row['supplier']); ?></td>
                                            <td class="text-center"><?= number_format($row['nilai'], 6); ?></td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <div class="card mb-3">
            <div class="card-header bg-success text-white">Supplier Rekomendasi</div>
            <div class="card-body">
                <?php if ($rekomendasiAhp && $rekomendasiFahp && $rekomendasiAhp['supplier'] === $rekomendasiFahp['supplier']): ?>
                    <p class="mb-0">
                        Supplier yang direkomendasikan adalah <strong><?= htmlspecialchars($rekomendasiAhp['supplier']); ?></strong>
                        karena menempati peringkat 1 pada metode AHP dan F-AHP.
                    </p>
                <?php elseif ($rekomendasiAhp || $rekomendasiFahp): ?>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>AHP:</strong>
                            <?= $rekomendasiAhp ? htmlspecialchars($rekomendasiAhp['supplier']) . ' (' . number_format($rekomendasiAhp['nilai'], 6) . ')' : '-'; ?>
                        </div>
                        <div class="col-md-6">
                            <strong>F-AHP:</strong>
                            <?= $rekomendasiFahp ? htmlspecialchars($rekomendasiFahp['supplier']) . ' (' . number_format($rekomendasiFahp['nilai'], 6) . ')' : '-'; ?>
                        </div>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">Rekomendasi supplier belum dapat ditentukan.</div>
                <?php endif; ?>
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-warning text-dark">Ringkasan Perbandingan</div>
            <div class="card-body">
                <?php if (empty($perbandingan)): ?>
                    <div class="alert alert-warning mb-0">Perbandingan metode belum dapat ditampilkan.</div>
                <?php else: ?>
                    <p><?= htmlspecialchars($kesimpulan); ?></p>
                    <div class="table-responsive">
                        <table class="table table-bordered table-striped align-middle mb-0">
                            <thead class="table-dark">
                                <tr>
                                    <th>Supplier</th>
                                    <th width="120">AHP</th>
                                    <th width="120">F-AHP</th>
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
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
