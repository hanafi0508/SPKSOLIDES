<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';
require_once '../../functions/ranking_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek");

$id_proyek = isset($_GET['proyek']) ? (int) $_GET['proyek'] : 0;

$data = [];
$workflowStatus = null;
$workflowIssues = [];
if ($id_proyek) {
    $stmt = mysqli_prepare($conn, "
        SELECT a.*, s.nama_supplier, s.tipe_supplier, s.jenis_material
        FROM alternatif a
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE a.id_proyek = ?
    ");
    mysqli_stmt_bind_param($stmt, "i", $id_proyek);
    mysqli_stmt_execute($stmt);
    $data = mysqli_stmt_get_result($stmt);

    $workflowStatus = getProjectWorkflowStatus($conn, $id_proyek);
    $workflowIssues = getProjectWorkflowIssues($workflowStatus);
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="page-toolbar">
        <div>
            <h3>Perhitungan Supplier</h3>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-body">
            <form method="GET">
                <label>Pilih Proyek</label>
                <select name="proyek" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Proyek --</option>
                    <?php while($p = mysqli_fetch_assoc($proyek)): ?>
                        <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek==$p['id_proyek']?'selected':''; ?>>
                            <?= htmlspecialchars($p['nama_proyek']); ?>
                        </option>
                    <?php endwhile; ?>
                </select>
            </form>
        </div>
    </div>

    <?php if ($id_proyek): ?>
        <?php if ($workflowStatus): ?>
            <div class="row g-3 mb-3">
                <div class="col-md-3">
                    <div class="card metric-card">
                        <span class="metric-label">Supplier Dipilih</span>
                        <strong class="metric-value"><?= $workflowStatus['alternatif']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card">
                        <span class="metric-label">Kriteria</span>
                        <strong class="metric-value"><?= $workflowStatus['kriteria']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card">
                        <span class="metric-label">Penilaian</span>
                        <strong class="metric-value"><?= $workflowStatus['penilaian_terisi']; ?>/<?= $workflowStatus['penilaian_harus']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card metric-card">
                        <span class="metric-label">Status Bobot</span>
                        <strong class="fs-5"><?= $workflowStatus['ahp_konsisten'] ? 'AHP Siap' : 'AHP Belum'; ?></strong>
                    </div>
                </div>
            </div>

            <?php if (!empty($workflowIssues)): ?>
                <div class="alert alert-warning">
                    <strong>Checklist alur belum lengkap:</strong>
                    <ul class="mb-0 mt-2">
                        <?php foreach ($workflowIssues as $issue): ?>
                            <li><?= htmlspecialchars($issue); ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
        <?php endif; ?>

        <div class="page-toolbar">
            <div></div>
            <a href="tambah.php?proyek=<?= $id_proyek; ?>" class="btn btn-primary">Tambah Supplier ke Perhitungan</a>
        </div>

        <div class="card">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle">
                        <thead>
                            <tr>
                                <th width="70">No</th>
                                <th>Supplier</th>
                                <th width="120">Tipe</th>
                                <th>Barang / Jasa</th>
                                <th width="110">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                            <tr>
                                <td class="text-center"><?= $no++; ?></td>
                                <td><?= htmlspecialchars($row['nama_supplier'] ?? '-'); ?></td>
                                <td class="text-center"><?= ucfirst(htmlspecialchars($row['tipe_supplier'] ?? 'barang')); ?></td>
                                <td><?= htmlspecialchars($row['jenis_material'] ?? '-'); ?></td>
                                <td class="text-center">
                                    <a href="hapus.php?id=<?= $row['id_alternatif']; ?>&proyek=<?= $id_proyek; ?>"
                                       class="btn btn-danger btn-sm"
                                       onclick="return confirm('Hapus alternatif?')">Hapus</a>
                                </td>
                            </tr>
                            <?php endwhile; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
