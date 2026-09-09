<?php
require_once '../functions/init.php';
require_once '../functions/ranking_function.php';
check_login();

$total = db_one($conn, "SELECT
    (SELECT COUNT(*) FROM proyek) AS proyek,
    (SELECT COUNT(*) FROM supplier) AS supplier,
    (SELECT COUNT(*) FROM kriteria) AS kriteria,
    (SELECT COUNT(*) FROM hasil_ranking) AS ranking
");

$total_proyek = (int) ($total['proyek'] ?? 0);
$total_supplier = (int) ($total['supplier'] ?? 0);
$total_kriteria = (int) ($total['kriteria'] ?? 0);
$total_ranking = (int) ($total['ranking'] ?? 0);

$laporan = getLaporanData($conn);
$rankingAhp = $laporan['ranking_ahp'];
$bobotAhp = $laporan['bobot_ahp'];
$skorSupplier = $laporan['skor_supplier'];
$rekomendasiAhp = $rankingAhp[0] ?? null;

$skor = [];
foreach ($skorSupplier as $row) {
    $skor[$row['id_kriteria']][$row['id_supplier']] = $row['skor'];
}

$medals = ['gold', 'silver', 'bronze'];
$medalBadge = [
    1 => 'bg-warning text-dark',
    2 => 'bg-secondary',
    3 => 'bg-info text-dark',
];

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h3 class="fw-bold">Dashboard</h3>
    </div>
</div>

<!-- ============ METRIK ============ -->
<div class="row g-3">
    <div class="col-md-3">
        <div class="card h-100 d-flex flex-column justify-content-between">
            <span class="text-uppercase fw-bold text-secondary small"><i class="bi bi-briefcase me-1"></i>Proyek</span>
            <strong class="fw-bolder fs-2 lh-1"><?= $total_proyek; ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 d-flex flex-column justify-content-between">
            <span class="text-uppercase fw-bold text-secondary small"><i class="bi bi-truck me-1"></i>Supplier</span>
            <strong class="fw-bolder fs-2 lh-1"><?= $total_supplier; ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 d-flex flex-column justify-content-between">
            <span class="text-uppercase fw-bold text-secondary small"><i class="bi bi-list-check me-1"></i>Kriteria</span>
            <strong class="fw-bolder fs-2 lh-1"><?= $total_kriteria; ?></strong>
        </div>
    </div>
    <div class="col-md-3">
        <div class="card h-100 d-flex flex-column justify-content-between">
            <span class="text-uppercase fw-bold text-secondary small"><i class="bi bi-trophy me-1"></i>Hasil Ranking</span>
            <strong class="fw-bolder fs-2 lh-1"><?= $total_ranking; ?></strong>
        </div>
    </div>
</div>

<!-- ============ HASIL KEPUTUSAN ============ -->
<div class="card mt-4" id="hasil">
    <div class="card-body">

        <?php if (!$rekomendasiAhp): ?>
            <div class="alert alert-warning">
                Belum ada hasil ranking. Alurnya: <strong>1.</strong> Kelola kriteria &amp; hitung bobot →
                <strong>2.</strong> Penilaian supplier (skor 1–9) → <strong>3.</strong> Hasil muncul di sini.
                <?php if ($_SESSION['level'] == 'admin'): ?>
                    <br><a href="<?= BASE_URL; ?>/pages/kriteria/index.php#bobot" class="btn btn-primary btn-sm mt-2">Atur Bobot Kriteria</a>
                    <a href="<?= BASE_URL; ?>/pages/ahp/input_alternatif.php" class="btn btn-info btn-sm mt-2">Penilaian Supplier</a>
                <?php endif; ?>
            </div>

        <?php else: ?>

            <!-- ===== PODIUM 3 BESAR ===== -->
            <?php
            $podium = array_slice($rankingAhp, 0, 3);
            // urutan tampilan podium: juara2 | juara1 | juara3
            $podiumOrder = [1, 0, 2];
            ?>
            <div class="podium-wrap mb-4">
                <div class="podium">
                    <?php foreach ($podiumOrder as $idx): ?>
                        <?php if (!isset($podium[$idx])) continue; ?>
                        <?php $p = $podium[$idx]; $medal = $medals[$idx]; ?>
                        <div class="podium-col podium-<?= $idx + 1; ?>">
                            <div class="podium-medal">
                                <i class="bi <?= $idx === 0 ? 'bi-trophy-fill' : 'bi-award-fill'; ?>"></i>
                            </div>
                            <div class="podium-name"><?= h($p['nama_supplier']); ?></div>
                            <div class="podium-value"><?= number_format((float) $p['nilai'], 4); ?></div>
                            <div class="podium-stand"><?= $p['ranking']; ?></div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>

            <div class="mb-3">
                <a href="hasil/cetak.php" target="_blank" class="btn btn-dark btn-icon-gap"><i class="bi bi-printer"></i> Cetak Laporan</a>
            </div>

            <!-- ===== TABEL RANKING LENGKAP ===== -->
            <h6 class="fw-bold">Ranking Supplier</h6>
            <div class="table-responsive mb-3">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th width="80">Ranking</th>
                            <th>Supplier</th>
                            <th>Barang</th>
                            <th>Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankingAhp as $r): ?>
                            <tr>
                                <td class="text-center">
                                    <?php if (isset($medalBadge[$r['ranking']])): ?>
                                        <span class="badge <?= $medalBadge[$r['ranking']]; ?>">
                                            <i class="bi bi-trophy-fill me-1"></i><?= $r['ranking']; ?>
                                        </span>
                                    <?php else: ?>
                                        <?= $r['ranking']; ?>
                                    <?php endif; ?>
                                </td>
                                <td><?= h($r['nama_supplier']); ?></td>
                                <td><?= h($r['jenis_material'] ?? '-'); ?></td>
                                <td class="text-center"><strong><?= number_format((float) $r['nilai'], 6); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- ===== SKOR SUPPLIER PER KRITERIA ===== -->
            <h6 class="mt-4 fw-bold">Skor Supplier per Kriteria (1–9)</h6>
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
                    <thead class="table-dark text-center">
                        <tr>
                            <th>Supplier</th>
                            <?php foreach ($bobotAhp as $b): ?>
                                <th>
                                    <?= h($b['nama_kriteria']); ?>
                                    <br><small class="text-white-50">Bobot: <?= number_format((float) $b['bobot'], 4); ?></small>
                                </th>
                            <?php endforeach; ?>
                            <th>Nilai Akhir</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($rankingAhp as $r): ?>
                            <tr>
                                <td><?= h($r['nama_supplier']); ?></td>
                                <?php foreach ($bobotAhp as $b):
                                    $skorVal = $skor[$b['id_kriteria']][$r['id_supplier']] ?? null;
                                ?>
                                    <td class="text-center">
                                        <?php if ($skorVal !== null): ?>
                                            <span class="badge <?= $skorVal >= 7 ? 'bg-success' : ($skorVal >= 4 ? 'bg-warning text-dark' : 'bg-danger'); ?>"><?= h($skorVal); ?></span>
                                        <?php else: ?>
                                            -
                                        <?php endif; ?>
                                    </td>
                                <?php endforeach; ?>
                                <td class="text-center"><strong><?= number_format((float) $r['nilai'], 6); ?></strong></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

        <?php endif; ?>
    </div>
</div>
<?php layout_bottom(); ?>