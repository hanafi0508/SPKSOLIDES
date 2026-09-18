<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$data = mysqli_query($conn, "SELECT * FROM kriteria ORDER BY kode_kriteria ASC");
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3">
        <h3 class="fw-bold mb-0">Data Kriteria</h3>
        <button type="button" class="btn btn-primary btn-add" data-bs-toggle="modal" data-bs-target="#modalKriteria">
            Tambah Kriteria
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
                            <th width="120">Kode</th>
                            <th>Nama</th>
                            <th width="140">Jenis</th>
                            <th width="170">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php $no = 1; while ($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td class="text-center"><strong><?= htmlspecialchars($row['kode_kriteria']); ?></strong></td>
                        <td><?= htmlspecialchars($row['nama_kriteria']); ?></td>
                        <td class="text-center">
                            <span class="badge <?= $row['jenis_kriteria'] == 'benefit' ? 'text-bg-success' : 'text-bg-warning'; ?>">
                                <?= ucfirst(htmlspecialchars($row['jenis_kriteria'])); ?>
                            </span>
                        </td>
                        <td class="text-center">
                            <div class="d-flex justify-content-center gap-1">
                                <button type="button" class="btn btn-warning btn-sm btn-edit"
                                        data-bs-toggle="modal" data-bs-target="#modalKriteria"
                                        data-id="<?= $row['id_kriteria']; ?>"
                                        data-kode="<?= htmlspecialchars($row['kode_kriteria']); ?>"
                                        data-nama="<?= htmlspecialchars($row['nama_kriteria']); ?>"
                                        data-jenis="<?= htmlspecialchars($row['jenis_kriteria']); ?>"
                                        data-deskripsi="<?= htmlspecialchars($row['deskripsi'] ?? ''); ?>">Edit</button>
                                <a href="hapus.php?id=<?= $row['id_kriteria']; ?>" class="btn btn-danger btn-sm"
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

<div class="modal fade" id="modalKriteria" tabindex="-1" aria-hidden="true" data-add-label="Kriteria">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <form method="POST" action="simpan.php">
                <div class="modal-header">
                    <h5 class="modal-title" id="modalKriteriaTitle">Tambah Kriteria</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Tutup"></button>
                </div>
                <div class="modal-body">
                    <input type="hidden" name="id" value="">
                    <div class="mb-3">
                        <label class="form-label">Kode</label>
                        <input type="text" name="kode" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Nama</label>
                        <input type="text" name="nama" class="form-control" required>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Jenis</label>
                        <select name="jenis" class="form-select">
                            <option value="benefit">Benefit</option>
                            <option value="cost">Cost</option>
                        </select>
                    </div>
                    <div class="mb-0">
                        <label class="form-label">Deskripsi</label>
                        <textarea name="deskripsi" class="form-control" rows="3"></textarea>
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