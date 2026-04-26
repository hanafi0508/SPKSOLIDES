<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$error = '';

if (isset($_POST['submit'])) {

    $nama = $_POST['nama'];
    $lokasi = $_POST['lokasi'];
    $mulai = $_POST['mulai'];
    $selesai = $_POST['selesai'];
    $ket = $_POST['keterangan'];

    if (empty($nama)) {
        $error = "Nama proyek wajib diisi";
    }
    elseif ($selesai < $mulai) {
        $error = "Tanggal selesai tidak boleh sebelum mulai";
    } else {

        mysqli_query($conn, "INSERT INTO proyek 
        (nama_proyek, lokasi, tanggal_mulai, tanggal_selesai, keterangan)
        VALUES ('$nama','$lokasi','$mulai','$selesai','$ket')");

        header("Location: index.php");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah Proyek</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Proyek</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="lokasi" class="form-control">
        </div>

        <div class="mb-3">
            <label>Tanggal Mulai</label>
            <input type="date" name="mulai" class="form-control">
        </div>

        <div class="mb-3">
            <label>Tanggal Selesai</label>
            <input type="date" name="selesai" class="form-control">
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control"></textarea>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
