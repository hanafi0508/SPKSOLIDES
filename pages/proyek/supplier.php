<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

$id_proyek = isset($_GET['id_proyek']) ? (int) $_GET['id_proyek'] : 0;
$proyek = db_one($conn, 'SELECT * FROM proyek WHERE id_proyek = ?', 'i', [$id_proyek]);

if (!$proyek) {
    header('Location: index.php');
    exit;
}

if (isset($_GET['hapus'])) {
    $id = (int) $_GET['hapus'];
    db_exec($conn, 'DELETE FROM proyek_supplier WHERE id = ?', 'i', [$id]);
    alertRedirect('Supplier dihapus dari proyek.', 'supplier.php?id_proyek=' . $id_proyek);
    exit;
}

$error = '';

if (isset($_POST['submit'])) {
    $id_supplier = (int) $_POST['supplier'];

    if (db_one($conn, 'SELECT id FROM proyek_supplier WHERE id_proyek = ? AND id_supplier = ?', 'ii', [$id_proyek, $id_supplier])) {
        $error = 'Supplier sudah terhubung dengan proyek ini';
    } else {
        db_exec($conn, 'INSERT INTO proyek_supplier (id_proyek, id_supplier) VALUES (?, ?)', 'ii', [$id_proyek, $id_supplier]);
        alertRedirect('Supplier berhasil ditambahkan ke proyek.', 'supplier.php?id_proyek=' . $id_proyek);
        exit;
    }
}

$data = db_all($conn, '
    SELECT ps.id, s.nama_supplier, s.jenis_material, s.no_telepon
    FROM proyek_supplier ps
    JOIN supplier s ON s.id_supplier = ps.id_supplier
    WHERE ps.id_proyek = ?
    ORDER BY s.nama_supplier ASC
', 'i', [$id_proyek]);

$terhubung = array_map('intval', array_column($data, 'id_supplier'));
$semuaSupplier = db_all($conn, 'SELECT * FROM supplier ORDER BY nama_supplier ASC');

$tersedia = array_filter($semuaSupplier, function ($s) use ($terhubung) {
    return !in_array((int) $s['id_supplier'], $terhubung, true);
});

layout_top();
?>
<div class="row mb-3">
    <div class="col-md-12">
        <h4>Supplier Proyek: <?= h($proyek['nama_proyek']); ?></h4>
        <p class="text-muted mb-0">Data pelengkap — mencatat supplier yang terlibat di proyek ini, tidak mempengaruhi penilaian AHP.</p>
    </div>
</div>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= h($error); ?></div>
<?php endif; ?>

<div class="card mb-3">
    <div class="card-header bg-primary text-white">Tambah Supplier ke Proyek</div>
    <div class="card-body">
        <?php if (!$tersedia): ?>
            <div class="alert alert-info mb-0">Semua supplier sudah terhubung dengan proyek ini.</div>
        <?php else: ?>
        <form method="POST" class="row g-2">
            <?php csrf_field(); ?>
            <div class="col-md-8">
                <select name="supplier" class="form-select" required>
                    <option value="">-- Pilih Supplier --</option>
                    <?php foreach ($tersedia as $s): ?>
                        <option value="<?= $s['id_supplier']; ?>">
                            <?= h($s['nama_supplier']); ?> - <?= h($s['jenis_material'] ?? '-'); ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="col-md-4">
                <button name="submit" class="btn btn-primary">Tambah</button>
            </div>
        </form>
        <?php endif; ?>
    </div>
</div>

<div class="card">
    <div class="card-header bg-info text-white">Daftar Supplier</div>
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-bordered table-striped align-middle mb-0">
                <thead>
                    <tr>
                        <th width="70">No</th>
                        <th>Supplier</th>
                        <th>Barang / Jasa</th>
                        <th width="180">Kontak</th>
                        <th width="110">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!$data): ?>
                        <tr><td colspan="5" class="text-center">Belum ada supplier pada proyek ini.</td></tr>
                    <?php else: $no = 1; foreach ($data as $row): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><?= h($row['nama_supplier']); ?></td>
                        <td><?= h($row['jenis_material'] ?? '-'); ?></td>
                        <td><?= h($row['no_telepon'] ?? '-'); ?></td>
                        <td class="text-center">
                            <a href="supplier.php?id_proyek=<?= $id_proyek; ?>&hapus=<?= $row['id']; ?>"
                               class="btn btn-danger btn-sm"
                               data-confirm="Yakin hapus supplier dari proyek ini?">Hapus</a>
                        </td>
                    </tr>
                    <?php endforeach; endif; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<div class="mt-3">
    <a href="index.php" class="btn btn-secondary">Kembali</a>
</div>
<?php layout_bottom(); ?>