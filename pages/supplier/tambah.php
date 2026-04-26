<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

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
        $error = "Format email tidak valid";
    }
    else {
        mysqli_query($conn, "INSERT INTO supplier 
        (nama_supplier, alamat, no_telepon, email, jenis_material, status)
        VALUES ('$nama','$alamat','$telp','$email','$material','$status')");

        header("Location: index.php");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah Supplier</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Supplier</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Alamat</label>
            <textarea name="alamat" class="form-control"></textarea>
        </div>

        <div class="mb-3">
            <label>No Telepon</label>
            <input type="text" name="telepon" class="form-control">
        </div>

        <div class="mb-3">
            <label>Email</label>
            <input type="text" name="email" class="form-control">
        </div>

        <div class="mb-3">
            <label>Jenis Material</label>
            <input type="text" name="material" class="form-control">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="aktif">Aktif</option>
                <option value="tidak_aktif">Tidak Aktif</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
