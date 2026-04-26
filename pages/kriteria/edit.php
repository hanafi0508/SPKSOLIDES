<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = $_GET['id'];

$data = mysqli_query($conn, "SELECT * FROM kriteria WHERE id_kriteria='$id'");
$row = mysqli_fetch_assoc($data);

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
        $error = "Jenis tidak valid";
    }
    else {

        mysqli_query($conn, "UPDATE kriteria SET
            kode_kriteria='$kode',
            nama_kriteria='$nama',
            jenis_kriteria='$jenis',
            deskripsi='$deskripsi'
            WHERE id_kriteria='$id'
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

    <h3>Edit Kriteria</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Kode</label>
            <input type="text" name="kode" class="form-control" value="<?= $row['kode_kriteria']; ?>">
        </div>

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= $row['nama_kriteria']; ?>">
        </div>

        <div class="mb-3">
            <label>Jenis</label>
            <select name="jenis" class="form-control">
                <option value="benefit" <?= $row['jenis_kriteria']=='benefit'?'selected':''; ?>>Benefit</option>
                <option value="cost" <?= $row['jenis_kriteria']=='cost'?'selected':''; ?>>Cost</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Deskripsi</label>
            <textarea name="deskripsi" class="form-control"><?= $row['deskripsi']; ?></textarea>
        </div>

        <button name="submit" class="btn btn-success">Update</button>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>