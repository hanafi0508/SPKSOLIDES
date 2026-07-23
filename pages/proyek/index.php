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

    <div class="page-toolbar">
        <div>
            <h3>Data Proyek</h3>
        </div>
        <a href="tambah.php" class="btn btn-primary">Tambah Proyek</a>
    </div>

    <div class="card">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-bordered table-striped align-middle">
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
                    <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
                    <tr>
                        <td class="text-center"><?= $no++; ?></td>
                        <td><strong><?= htmlspecialchars($row['nama_proyek']); ?></strong></td>
                        <td><?= htmlspecialchars($row['lokasi'] ?? '-'); ?></td>
                        <td><?= htmlspecialchars($row['tanggal_mulai'] ?? '-'); ?> - <?= htmlspecialchars($row['tanggal_selesai'] ?? '-'); ?></td>
                        <td class="text-center">
                            <a href="edit.php?id=<?= $row['id_proyek']; ?>" class="btn btn-warning btn-sm">Edit</a>
                            <a href="hapus.php?id=<?= $row['id_proyek']; ?>" class="btn btn-danger btn-sm"
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
