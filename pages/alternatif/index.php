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
$supplierAktif = [];
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

    $supplierAktif = mysqli_query($conn, "
        SELECT * FROM supplier
        WHERE status = 'aktif'
        ORDER BY tipe_supplier ASC, nama_supplier ASC
    ");

    $workflowStatus = getProjectWorkflowStatus($conn, $id_proyek);
    $workflowIssues = getProjectWorkflowIssues($workflowStatus);
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="mb-3">
        <h3 class="fw-bold mb-0">Perhitungan Supplier</h3>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm mb-3">
        <div class="card-body">
            <label class="form-label">Pilih Proyek</label>
            <form method="GET">
                <select name="proyek" class="form-select" onchange="this.form.submit()">
                    <option value="">-- Pilih Proyek --</option>
                    <?php while ($p = mysqli_fetch_assoc($proyek)): ?>
                        <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek == $p['id_proyek'] ? 'selected' : ''; ?>>
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
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <span class="text-uppercase small fw-semibold text-secondary">Supplier Dipilih</span>
                            <strong class="fs-2 fw-bold"><?= $workflowStatus['alternatif']; ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <span class="text-uppercase small fw-semibold text-secondary">Kriteria</span>
                            <strong class="fs-2 fw-bold"><?= $workflowStatus['kriteria']; ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <span class="text-uppercase small fw-semibold text-secondary">Penilaian</span>
                            <strong class="fs-2 fw-bold"><?= $workflowStatus['penilaian_terisi']; ?>/<?= $workflowStatus['penilaian_harus']; ?></strong>
                        </div>
                    </div>
                </div>
                <div class="col-md-3">
                    <div class="card shadow-sm h-100">
                        <div class="card-body d-flex flex-column justify-content-between">
                            <span class="text-uppercase small fw-semibold text-secondary">Status Bobot</span>
                            <strong class="fs-5 fw-bold"><?= $workflowStatus['ahp_konsisten'] ? 'AHP Siap' : 'AHP Belum'; ?></strong>
                        </div>
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

        <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
            <div></div>
            <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#modalAlternatif">
                Tambah Supplier ke Perhitungan
            </button>
        </div>

        <div class="card shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered table-striped align-middle mb-0">
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
                            <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
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

        <div class="modal fade" id="modalAlternatif" tabindex="-1" aria-hidden="true" data-add-label="Supplier ke Perhitungan">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <form method="POST" action="simpan.php">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalAlternatifTitle">Tambah Supplier ke Perhitungan</h5>
                            <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                        </div>
                        <div class="modal-body">
                            <input type="hidden" name="proyek" value="<?= $id_proyek; ?>">
                            <div class="mb-0">
                                <label class="form-label">Pilih Supplier</label>
                                <select name="supplier" class="form-select" required>
                                    <option value="">-- Pilih Supplier --</option>
                                    <?php while ($s = mysqli_fetch_assoc($supplierAktif)): ?>
                                        <option value="<?= $s['id_supplier']; ?>">
                                            <?= htmlspecialchars($s['nama_supplier'] ?? '-'); ?>
                                            - <?= ucfirst(htmlspecialchars($s['tipe_supplier'] ?? 'barang')); ?>
                                            - <?= htmlspecialchars($s['jenis_material'] ?? '-'); ?>
                                        </option>
                                    <?php endwhile; ?>
                                </select>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                            <button type="submit" name="submit" class="btn btn-primary">Simpan</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>