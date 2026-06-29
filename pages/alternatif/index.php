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

    <h3>Perhitungan Supplier</h3>

    <form method="GET" class="mb-3">
        <select name="proyek" class="form-control" onchange="this.form.submit()">
            <option value="">-- Pilih Proyek --</option>
            <?php while($p = mysqli_fetch_assoc($proyek)): ?>
                <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek==$p['id_proyek']?'selected':''; ?>>
                    <?= $p['nama_proyek']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($id_proyek): ?>
        <?php if ($workflowStatus): ?>
            <div class="row mb-3">
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Supplier Dipilih</small>
                        <strong><?= $workflowStatus['alternatif']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Kriteria</small>
                        <strong><?= $workflowStatus['kriteria']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Penilaian</small>
                        <strong><?= $workflowStatus['penilaian_terisi']; ?> / <?= $workflowStatus['penilaian_harus']; ?></strong>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card p-3">
                        <small class="text-muted">Status Bobot</small>
                        <strong>AHP <?= $workflowStatus['ahp_konsisten'] ? 'Siap' : 'Belum'; ?> / F-AHP <?= $workflowStatus['fahp_total'] > 0 ? 'Siap' : 'Belum'; ?></strong>
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

        <a href="tambah.php?proyek=<?= $id_proyek; ?>" class="btn btn-primary mb-3">Tambah Supplier ke Perhitungan</a>

        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Supplier</th>
                <th>Tipe</th>
                <th>Barang / Jasa</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= htmlspecialchars($row['nama_supplier']); ?></td>
                <td><?= ucfirst(htmlspecialchars($row['tipe_supplier'] ?? 'barang')); ?></td>
                <td><?= htmlspecialchars($row['jenis_material']); ?></td>
                <td>
                    <a href="hapus.php?id=<?= $row['id_alternatif']; ?>&proyek=<?= $id_proyek; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus alternatif?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
