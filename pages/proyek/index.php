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

    <h3>Data Proyek</h3>

    <a href="tambah.php" class="btn btn-primary mb-3">Tambah</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama Proyek</th>
                <th>Lokasi</th>
                <th>Tanggal</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['nama_proyek']; ?></td>
            <td><?= $row['lokasi']; ?></td>
            <td><?= $row['tanggal_mulai']; ?> - <?= $row['tanggal_selesai']; ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_proyek']; ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $row['id_proyek']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include '../../layouts/footer.php'; ?>