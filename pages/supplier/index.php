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

    <h3>Data Supplier</h3>

    <a href="tambah.php" class="btn btn-primary mb-3">Tambah</a>

    <table class="table table-bordered table-striped">
        <thead class="table-dark">
            <tr>
                <th>No</th>
                <th>Nama</th>
                <th>Telepon</th>
                <th>Email</th>
                <th>Material</th>
                <th>Status</th>
                <th width="150">Aksi</th>
            </tr>
        </thead>

        <tbody>
        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['nama_supplier']; ?></td>
            <td><?= $row['no_telepon']; ?></td>
            <td><?= $row['email']; ?></td>
            <td><?= $row['jenis_material']; ?></td>
            <td>
                <span class="badge bg-<?= $row['status']=='aktif' ? 'success' : 'secondary'; ?>">
                    <?= ucfirst($row['status']); ?>
                </span>
            </td>
            <td>
                <a href="edit.php?id=<?= $row['id_supplier']; ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $row['id_supplier']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>
        </tbody>
    </table>

</div>

<?php include '../../layouts/footer.php'; ?>