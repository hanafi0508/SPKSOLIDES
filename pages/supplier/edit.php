<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];
$data = mysqli_query($conn, "SELECT * FROM supplier WHERE id_supplier='$id'");
$row = mysqli_fetch_assoc($data);

$error = '';

if (isset($_POST['submit'])) {

    $nama = $_POST['nama'];
    $alamat = $_POST['alamat'];
    $telp = $_POST['telepon'];
    $email = $_POST['email'];
    $material = $_POST['material'];
    $status = $_POST['status'];

    if (empty($nama)) {
        $error = "Nama supplier wajib diisi";
    }
    elseif (!empty($email) && !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Email tidak valid";
    }
    else {

        mysqli_query($conn, "UPDATE supplier SET
            nama_supplier='$nama',
            alamat='$alamat',
            no_telepon='$telp',
            email='$email',
            jenis_material='$material',
            status='$status'
            WHERE id_supplier='$id'
        ");

        header("Location: index.php");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Edit Supplier</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Supplier</label>
            <input type="text" name="nama" class="form-control" value="<?= $row['nama_supplier']; ?>">
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"><?= $row['alamat']; ?></textarea>
        </div>

        <div class="mb-3">
            <label>No Telepon</label>
            <input type="text" name="telepon" class="form-control" value="<?= $row['no_telepon']; ?>">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="text" name="email" class="form-control" value="<?= $row['email']; ?>">
        </div>

        <div class="mb-3">
            <label>Jenis Material</label>
            <input type="text" name="material" class="form-control" value="<?= $row['jenis_material']; ?>">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="aktif" <?= $row['status']=='aktif'?'selected':''; ?>>Aktif</option>
                <option value="tidak_aktif" <?= $row['status']=='tidak_aktif'?'selected':''; ?>>Tidak Aktif</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Update</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
