<?php
require_once '../../functions/init.php';
require_auth('admin');

$q = trim($_GET['q'] ?? '');

$where = '';
$params = [];
$types = '';

if ($q !== '') {
    $where = 'WHERE (nama_supplier LIKE ? OR jenis_material LIKE ? OR no_telepon LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like, $like];
    $types = 'sss';
}

$data = db_all($conn, 'SELECT * FROM supplier ' . $where . ' ORDER BY nama_supplier ASC');

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="fw-bold">Data Supplier</h4>
    </div>
    <div class="d-flex flex-wrap gap-2 page-actions align-items-center">
        <form method="GET" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="<?= h($q); ?>" class="form-control" placeholder="Cari nama, barang, kontak...">
            </div>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
        </form>
        <button type="button" class="btn btn-primary btn-icon-gap" data-bs-toggle="modal" data-bs-target="#modalSupplier">
            <i class="bi bi-plus-lg"></i> Tambah Supplier
        </button>
    </div>
</div>

<?php if (!$data): ?>
    <div class="alert alert-info">Tidak ada data supplier.</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Supplier</th>
                        <th width="170">Kontak</th>
                        <th>Barang</th>
                        <th width="110">Status</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= h($row['nama_supplier']); ?></td>
                        <td><?= h($row['no_telepon'] ?? '-'); ?></td>
                        <td><?= h($row['jenis_material'] ?? '-'); ?></td>
                        <td class="text-center">
                            <span class="badge bg-<?= $row['status'] == 'aktif' ? 'success' : 'secondary'; ?>">
                                <?= h(ucfirst($row['status'])); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalSupplier"
                                    data-id="<?= (int) $row['id_supplier']; ?>"
                                    data-nama="<?= h($row['nama_supplier']); ?>"
                                    data-telepon="<?= h($row['no_telepon'] ?? ''); ?>"
                                    data-email="<?= h($row['email'] ?? ''); ?>"
                                    data-material="<?= h($row['jenis_material'] ?? ''); ?>"
                                    data-status="<?= h($row['status']); ?>">Edit</button>
                            <a href="hapus.php?id=<?= (int) $row['id_supplier']; ?>" class="btn btn-danger btn-sm"
                               data-confirm="Yakin hapus supplier <?= h($row['nama_supplier']); ?>?">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL TAMBAH / EDIT SUPPLIER -->
<div class="modal fade" id="modalSupplier" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="simpan.php" id="formSupplier">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="sup_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalSupTitle">Tambah Supplier</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Supplier</label>
                        <input type="text" name="nama" id="sup_nama" class="form-control" required>
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label>Kontak</label>
                            <input type="text" name="telepon" id="sup_telepon" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Email</label>
                            <input type="email" name="email" id="sup_email" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Barang</label>
                        <input type="text" name="material" id="sup_material" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Status</label>
                        <select name="status" id="sup_status" class="form-control">
                            <option value="aktif">Aktif</option>
                            <option value="tidak_aktif">Tidak Aktif</option>
                        </select>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Batal</button>
                    <button name="submit" class="btn btn-primary">Simpan</button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
document.getElementById('modalSupplier').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const hasData = btn.dataset.id;
    document.getElementById('modalSupTitle').textContent = hasData ? 'Edit Supplier' : 'Tambah Supplier';
    document.getElementById('sup_id').value = hasData || '';
    document.getElementById('sup_nama').value = btn.dataset.nama || '';
    document.getElementById('sup_telepon').value = btn.dataset.telepon || '';
    document.getElementById('sup_email').value = btn.dataset.email || '';
    document.getElementById('sup_material').value = btn.dataset.material || '';
    document.getElementById('sup_status').value = btn.dataset.status || 'aktif';
});
document.getElementById('modalSupplier').addEventListener('hidden.bs.modal', function() {
    document.getElementById('sup_id').value = '';
    document.getElementById('formSupplier').reset();
});
</script>
<?php layout_bottom(); ?>