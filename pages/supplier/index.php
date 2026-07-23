<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$tipe = $_GET['tipe'] ?? 'barang';
if (!in_array($tipe, ['barang', 'jasa'], true)) {
    $tipe = 'barang';
}

$stmt = mysqli_prepare($conn, "SELECT * FROM supplier WHERE tipe_supplier = ? ORDER BY id_supplier ASC");
mysqli_stmt_bind_param($stmt, "s", $tipe);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
$judulTipe = $tipe === 'barang' ? 'Barang' : 'Jasa';
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="page-toolbar">
        <div>
            <h4>Data Supplier <?= htmlspecialchars($judulTipe); ?></h4>
            <p class="text-muted mb-0">Kelola supplier berdasarkan tipe barang atau jasa.</p>
        </div>
        <div class="d-flex gap-2 page-actions">
            <a href="index.php?tipe=barang" class="btn <?= $tipe === 'barang' ? 'btn-primary' : 'btn-outline-primary'; ?>">Barang</a>
            <a href="index.php?tipe=jasa" class="btn <?= $tipe === 'jasa' ? 'btn-primary' : 'btn-outline-primary'; ?>">Jasa</a>
            <a href="tambah.php?tipe=<?= urlencode($tipe); ?>" class="btn btn-primary">Tambah Supplier</a>
        </div>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle mb-0">
                    <thead class="text-center">
                        <tr>
                            <th width="70">No</th>
                            <th>Nama Supplier</th>
                            <th width="120">Tipe</th>
                            <th width="180">Kontak</th>
                            <th>Barang / Jasa</th>
                            <th width="120">Status</th>
                            <th width="150">Aksi</th>
                        </tr>
                    </thead>

                    <tbody>
                    <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_supplier']); ?></td>
                        <td class="text-center">
                            <span class="badge bg-primary-subtle text-dark border">
                                <?= ucfirst(htmlspecialchars($row['tipe_supplier'] ?? 'barang')); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['no_telepon'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['jenis_material'] ?? '-'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $row['status']=='aktif' ? 'success' : 'secondary'; ?>">
                                <?= ucfirst($row['status']); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['id_supplier']; ?>&tipe=<?= urlencode($tipe); ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_supplier']; ?>&tipe=<?= urlencode($tipe); ?>" class="btn btn-danger btn-sm"
                               onclick="return confirm('Yakin hapus?')">Hapus</a>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<?php include '../../layouts/footer.php'; ?>
