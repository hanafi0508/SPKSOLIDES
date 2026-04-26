<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM proyek WHERE id_proyek='$id'");
$row = mysqli_fetch_assoc($data);

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
        $error = "Tanggal tidak valid";
    } else {

        mysqli_query($conn, "UPDATE proyek SET
            nama_proyek='$nama',
            lokasi='$lokasi',
            tanggal_mulai='$mulai',
            tanggal_selesai='$selesai',
            keterangan='$ket'
            WHERE id_proyek='$id'
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

    <h3>Edit Proyek</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Proyek</label>
            <input type="text" name="nama" class="form-control" value="<?= $row['nama_proyek']; ?>">
        </div>

        <div class="mb-3">
            <label>Lokasi</label>
            <input type="text" name="lokasi" class="form-control" value="<?= $row['lokasi']; ?>">
        </div>

        <div class="mb-3">
            <label>Tanggal Mulai</label>
            <input type="date" name="mulai" class="form-control" value="<?= $row['tanggal_mulai']; ?>">
        </div>

        <div class="mb-3">
            <label>Tanggal Selesai</label>
            <input type="date" name="selesai" class="form-control" value="<?= $row['tanggal_selesai']; ?>">
        </div>

        <div class="mb-3">
            <label>Keterangan</label>
            <textarea name="keterangan" class="form-control"><?= $row['keterangan']; ?></textarea>
        </div>

        <button name="submit" class="btn btn-success">Update</button>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>