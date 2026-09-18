<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$data = mysqli_query($conn, "SELECT * FROM proyek ORDER BY id_proyek DESC");
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="fw-bold mb-0">Data Proyek</h3>
        <button type="button" class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#modalProyek">
            Tambah Proyek
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
                    <thead>
                        <tr>
                            <th width="70">No</th>
                            <th>Nama Proyek</th>
                            <th>Lokasi</th>
                            <th width="230">Tanggal</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_proyek']); ?></strong></td>
                        <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?> - <?= htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?></td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-warning btn-sm btn-edit"
                                        data-bs-toggle="modal" data-bs-target="#modalProyek"
                                        data-id="<?= $row['id_proyek']; ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_proyek']); ?>"
                                        data-lokasi="<?= htmlspecialchars($row['lokasi'] ?? ''); ?>"
                                        data-mulai="<?= htmlspecialchars($row['tanggal_mulai'] ?? ''); ?>"
                                        data-selesai="<?= htmlspecialchars($row['tanggal_selesai'] ?? ''); ?>"
                                        data-keterangan="<?= htmlspecialchars($row['keterangan'] ?? ''); ?>">Edit</button>
                                <a href="hapus.php?id=<?= $row['id_proyek']; ?>" class="btn btn-danger btn-sm"
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

<div class="modal fade" id="modalProyek" tabindex="-1" aria-hidden="true" data-add-label="Proyek">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="simpan.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalProyekTitle">Tambah Proyek</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="mb-3">
                        <label class="form-label">Nama Proyek</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Lokasi</label>
                        <input type="text" name="lokasi" class="form-control">
                    </div>
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Mulai</label>
                            <input type="date" name="mulai" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Tanggal Selesai</label>
                            <input type="date" name="selesai" class="form-control">
                        </div>
                    </div>
                    <div class="mt-3 mb-0">
                        <label class="form-label">Keterangan</label>
                        <textarea name="keterangan" class="form-control" rows="3"></textarea>
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