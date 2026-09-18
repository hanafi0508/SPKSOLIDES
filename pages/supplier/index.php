<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$data = mysqli_query($conn, "SELECT * FROM supplier ORDER BY id_supplier ASC");
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <div>
            <h3 class="fw-bold mb-0">Data Supplier</h3>
        </div>
        <button type="button" class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#modalSupplier">
            Tambah Supplier
        </button>
    </div>

    <?php if (isset($_GET['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <?= htmlspecialchars($_GET['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <div class="card shadow-sm">
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
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= htmlspecialchars($row['nama_supplier']); ?></td>
                        <td class="text-center">
                            <span class="badge text-bg-primary-subtle border">
                                <?= ucfirst(htmlspecialchars($row['tipe_supplier'] ?? 'barang')); ?>
                            </span>
                        </td>
                        <td><?= htmlspecialchars($row['no_telepon'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['jenis_material'] ?? '-'); ?></td>
                        <td class="text-center">
                            <span class="badge <?= $row['status'] == 'aktif' ? 'text-bg-success' : 'text-bg-secondary'; ?>">
                                <?= ucfirst(htmlspecialchars($row['status'])); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-warning btn-sm btn-edit"
                                        data-bs-toggle="modal" data-bs-target="#modalSupplier"
                                        data-id="<?= $row['id_supplier']; ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_supplier']); ?>"
                                        data-tipe="<?= htmlspecialchars($row['tipe_supplier'] ?? 'barang'); ?>"
                                        data-telepon="<?= htmlspecialchars($row['no_telepon'] ?? ''); ?>"
                                        data-material="<?= htmlspecialchars($row['jenis_material'] ?? ''); ?>"
                                        data-status="<?= htmlspecialchars($row['status'] ?? 'aktif'); ?>">Edit</button>
                                <a href="hapus.php?id=<?= $row['id_supplier']; ?>" class="btn btn-danger btn-sm"
                                   onclick="return confirm('Yakin hapus?')">Hapus</a>
                            </div>
                        </td>
                    </tr>
                    <?php endwhile; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>

</div>

<div class="modal fade" id="modalSupplier" tabindex="-1" aria-hidden="true" data-add-label="Supplier">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="simpan.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSupplierTitle">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="mb-3">
                        <label class="form-label">Nama Supplier</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Tipe Supplier</label>
                        <select name="tipe" class="form-select">
                            <option value="barang">Barang</option>
                            <option value="jasa">Jasa</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Kontak</label>
                        <input type="text" name="telepon" class="form-control">
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Barang / Jasa</label>
                        <input type="text" name="material" class="form-control" required>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Status</label>
                        <select name="status" class="form-select">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
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

<?php include '../../layouts/footer.php'; ?>