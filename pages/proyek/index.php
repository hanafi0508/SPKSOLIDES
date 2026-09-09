<?php
require_once '../../functions/init.php';
require_auth('admin');

$q = trim($_GET['q'] ?? '');

$where = '';
$params = [];
$types = '';

if ($q !== '') {
    $where = 'WHERE (nama_proyek LIKE ? OR lokasi LIKE ?)';
    $like = '%' . $q . '%';
    $params = [$like, $like];
    $types = 'ss';
}

$data = db_all($conn, 'SELECT * FROM proyek ' . $where . ' ORDER BY nama_proyek ASC');

layout_top();
?>
<div class="d-flex flex-wrap align-items-center justify-content-between gap-2 mb-3">
    <div>
        <h4 class="fw-bold">Data Proyek</h4>
    </div>
    <div class="d-flex flex-wrap gap-2 page-actions align-items-center">
        <form method="GET" class="d-flex gap-2">
            <div class="input-group">
                <span class="input-group-text"><i class="bi bi-search"></i></span>
                <input type="text" name="q" value="<?= h($q); ?>" class="form-control" placeholder="Cari nama proyek, lokasi...">
            </div>
            <button type="submit" class="btn btn-outline-secondary">Cari</button>
        </form>
        <button type="button" class="btn btn-primary btn-icon-gap" data-bs-toggle="modal" data-bs-target="#modalProyek">
            <i class="bi bi-plus-lg"></i> Tambah Proyek
        </button>
    </div>
</div>

<?php if (!$data): ?>
    <div class="alert alert-info">Tidak ada data proyek.</div>
<?php else: ?>
<div class="card">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead class="text-center">
                    <tr>
                        <th width="60">No</th>
                        <th>Nama Proyek</th>
                        <th>Lokasi</th>
                        <th width="130">Mulai</th>
                        <th width="130">Selesai</th>
                        <th width="150">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= h($row['nama_proyek']); ?></td>
                        <td><?= h($row['lokasi'] ?? '-'); ?></td>
                        <td class="text-center"><?= h($row['tanggal_mulai'] ?? '-'); ?></td>
                        <td class="text-center"><?= h($row['tanggal_selesai'] ?? '-'); ?></td>
                        <td class="text-center">
                            <button type="button" class="btn btn-warning btn-sm"
                                    data-bs-toggle="modal" data-bs-target="#modalProyek"
                                    data-id="<?= (int) $row['id_proyek']; ?>"
                                    data-nama="<?= h($row['nama_proyek']); ?>"
                                    data-lokasi="<?= h($row['lokasi'] ?? ''); ?>"
                                    data-mulai="<?= h($row['tanggal_mulai'] ?? ''); ?>"
                                    data-selesai="<?= h($row['tanggal_selesai'] ?? ''); ?>"
                                    data-keterangan="<?= h($row['keterangan'] ?? ''); ?>">Edit</button>
                            <a href="hapus.php?id=<?= (int) $row['id_proyek']; ?>" class="btn btn-danger btn-sm"
                               data-confirm="Yakin hapus proyek <?= h($row['nama_proyek']); ?>?">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php endif; ?>

<!-- MODAL TAMBAH / EDIT PROYEK -->
<div class="modal fade" id="modalProyek" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="POST" action="simpan.php" id="formProyek">
                <?php csrf_field(); ?>
                <input type="hidden" name="id" id="proy_id">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProyekTitle">Tambah Proyek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                </div>
                <div class="modal-body">
                    <div class="mb-3">
                        <label>Nama Proyek</label>
                        <input type="text" name="nama" id="proy_nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label>Lokasi</label>
                        <input type="text" name="lokasi" id="proy_lokasi" class="form-control">
                    </div>
                    <div class="row g-2">
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Mulai</label>
                            <input type="date" name="mulai" id="proy_mulai" class="form-control">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label>Tanggal Selesai</label>
                            <input type="date" name="selesai" id="proy_selesai" class="form-control">
                        </div>
                    </div>
                    <div class="mb-3">
                        <label>Keterangan</label>
                        <textarea name="keterangan" id="proy_keterangan" class="form-control" rows="3"></textarea>
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
document.getElementById('modalProyek').addEventListener('show.bs.modal', function(e) {
    const btn = e.relatedTarget;
    const hasData = btn.dataset.id;
    document.getElementById('modalProyekTitle').textContent = hasData ? 'Edit Proyek' : 'Tambah Proyek';
    document.getElementById('proy_id').value = hasData || '';
    document.getElementById('proy_nama').value = btn.dataset.nama || '';
    document.getElementById('proy_lokasi').value = btn.dataset.lokasi || '';
    document.getElementById('proy_mulai').value = btn.dataset.mulai || '';
    document.getElementById('proy_selesai').value = btn.dataset.selesai || '';
    document.getElementById('proy_keterangan').value = btn.dataset.keterangan || '';
});
document.getElementById('modalProyek').addEventListener('hidden.bs.modal', function() {
    document.getElementById('proy_id').value = '';
    document.getElementById('formProyek').reset();
});
</script>
<?php layout_bottom(); ?>