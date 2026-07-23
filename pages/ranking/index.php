<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;
$errorAhp = '';
$hasilAhp = null;
$rekomendasiAhp = null;
$workflowStatus = null;
$workflowIssues = [];

$proyekList = mysqli_query($conn, "SELECT id_proyek, nama_proyek FROM proyek ORDER BY id_proyek DESC");

if ($id_proyek > 0) {
    $workflowStatus = getProjectWorkflowStatus($conn, $id_proyek);
    $workflowIssues = getProjectWorkflowIssues($workflowStatus);

    try {
        $hasilAhp = prosesRankingAhp($conn, $id_proyek);
        $rankingAhp = array_values($hasilAhp['ranking']);
        $rekomendasiAhp = $rankingAhp[0] ?? null;
    } catch (Throwable $th) {
        $errorAhp = $th->getMessage();
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
            <a href="detail.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-outline-primary">Detail AHP</a>
            <a href="../laporan/index.php?id_proyek=<?= $id_proyek; ?>" class="btn btn-dark">Laporan</a>
        </div>

        <?php if ($workflowStatus): ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Supplier</small>
                        <strong><?= $workflowStatus['alternatif']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Penilaian</small>
                        <strong><?= $workflowStatus['penilaian_terisi']; ?>/<?= $workflowStatus['penilaian_harus']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">AHP</small>
                        <strong><?= $workflowStatus['ahp_konsisten'] ? 'Konsisten' : 'Belum Siap'; ?></strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($workflowIssues)): ?>
                <div class="alert alert-warning">
                    <strong>Flow proyek ini belum lengkap:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($workflowIssues as $issue): ?>
                            <li><?= htmlspecialchars($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>
    <?php endif; ?>

    <?php if ($errorAhp !== ''): ?>
        <div class="alert alert-warning">AHP: <?= htmlspecialchars($errorAhp); ?></div>
    <?php endif; ?>

    <?php if ($hasilAhp !== null): ?>
        <div class="card mb-3">
            <div class="card-header bg-primary text-white">Ranking AHP</div>
            <div class="card-body">
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
            </div>
        </div>

        <div class="card">
            <div class="card-header bg-success text-white">Supplier Rekomendasi</div>
            <div class="card-body">
                <?php if ($rekomendasiAhp): ?>
                    <p class="mb-0">
                        Supplier yang direkomendasikan adalah <strong><?= htmlspecialchars($rekomendasiAhp['supplier']); ?></strong>
                        dengan nilai akhir <?= number_format($rekomendasiAhp['nilai'], 6); ?>.
                    </p>
                <?php else: ?>
                    <div class="alert alert-warning mb-0">Rekomendasi supplier belum dapat ditentukan.</div>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
