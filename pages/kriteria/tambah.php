<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$error = '';

if (isset($_POST['submit'])) {

    $kode = strtoupper($_POST['kode']);
    $nama = $_POST['nama'];
    $jenis = $_POST['jenis'];
    $deskripsi = $_POST['deskripsi'];

    if (empty($kode)) {
        $error = "Kode wajib diisi";
    }
    elseif (empty($nama)) {
        $error = "Nama wajib diisi";
    }
    elseif (!in_array($jenis, ['benefit','cost'])) {
        $error = "Jenis harus benefit atau cost";
    }
    else {

        $cek = mysqli_query($conn, "SELECT * FROM kriteria WHERE kode_kriteria='$kode'");
        if (mysqli_num_rows($cek) > 0) {
            $error = "Kode sudah digunakan";
        } else {

            mysqli_query($conn, "INSERT INTO kriteria
            (kode_kriteria, nama_kriteria, jenis_kriteria, deskripsi)
            VALUES ('$kode','$nama','$jenis','$deskripsi')");

            header("Location: index.php");
            exit;
        }
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah Kriteria</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Jenis</label>
            <select name="jenis" class="form-control">
                <option value="benefit">Benefit</option>
                <option value="cost">Cost</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"></textarea>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
