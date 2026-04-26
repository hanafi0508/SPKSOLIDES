<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$data = mysqli_query($conn, "SELECT * FROM users");
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Data User</h3>

    <a href="tambah.php" class="btn btn-primary mb-3">Tambah</a>

    <table class="table table-bordered">
        <tr>
            <th>No</th>
            <th>Nama</th>
            <th>Username</th>
            <th>Level</th>
            <th>Aksi</th>
        </tr>

        <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
        <tr>
            <td><?= $no++; ?></td>
            <td><?= $row['nama_user']; ?></td>
            <td><?= $row['username']; ?></td>
            <td><?= $row['level']; ?></td>
            <td>
                <a href="edit.php?id=<?= $row['id_user']; ?>" class="btn btn-warning btn-sm">Edit</a>
                <a href="hapus.php?id=<?= $row['id_user']; ?>" class="btn btn-danger btn-sm"
                   onclick="return confirm('Yakin hapus?')">Hapus</a>
            </td>
        </tr>
        <?php endwhile; ?>

    </table>

</div>

<?php include '../../layouts/footer.php'; ?>