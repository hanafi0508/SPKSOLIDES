<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$proyek = mysqli_query($conn, "SELECT * FROM proyek");

$id_proyek = $_GET['proyek'] ?? '';

$data = [];
if ($id_proyek) {
    $data = mysqli_query($conn, "
        SELECT a.*, s.nama_supplier 
        FROM alternatif a
        JOIN supplier s ON a.id_supplier = s.id_supplier
        WHERE a.id_proyek = '$id_proyek'
    ");
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Alternatif Supplier</h3>

    <form method="GET" class="mb-3">
        <select name="proyek" class="form-control" onchange="this.form.submit()">
            <option value="">-- Pilih Proyek --</option>
            <?php while($p = mysqli_fetch_assoc($proyek)): ?>
                <option value="<?= $p['id_proyek']; ?>" <?= $id_proyek==$p['id_proyek']?'selected':''; ?>>
                    <?= $p['nama_proyek']; ?>
                </option>
            <?php endwhile; ?>
        </select>
    </form>

    <?php if ($id_proyek): ?>
        <a href="tambah.php?proyek=<?= $id_proyek; ?>" class="btn btn-primary mb-3">Tambah Alternatif</a>

        <table class="table table-bordered">
            <tr>
                <th>No</th>
                <th>Supplier</th>
                <th>Aksi</th>
            </tr>

            <?php $no=1; while($row = mysqli_fetch_assoc($data)): ?>
            <tr>
                <td><?= $no++; ?></td>
                <td><?= $row['nama_supplier']; ?></td>
                <td>
                    <a href="hapus.php?id=<?= $row['id_alternatif']; ?>&proyek=<?= $id_proyek; ?>"
                       class="btn btn-danger btn-sm"
                       onclick="return confirm('Hapus alternatif?')">Hapus</a>
                </td>
            </tr>
            <?php endwhile; ?>

        </table>
    <?php endif; ?>

</div>

<?php include '../../layouts/footer.php'; ?>
