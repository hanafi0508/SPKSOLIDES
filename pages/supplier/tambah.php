<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$error = '';
$tipe = $_GET['tipe'] ?? ($_POST['tipe_supplier'] ?? 'barang');

if (!in_array($tipe, ['barang', 'jasa'], true)) {
    $tipe = 'barang';
}

if (isset($_POST['submit'])) {

    $nama = trim($_POST['nama']);
    $tipe = $_POST['tipe_supplier'] ?? 'barang';
    $telp = trim($_POST['telepon']);
    $material = trim($_POST['material']);
    $status = $_POST['status'];

    if (empty($nama)) {
        $error = "Nama supplier wajib diisi";
    }
    elseif (!in_array($tipe, ['barang', 'jasa'], true)) {
        $error = "Tipe supplier tidak valid";
    }
    elseif (empty($material)) {
        $error = "Barang / jasa wajib diisi";
    }
    else {
        $stmt = mysqli_prepare($conn, "
            INSERT INTO supplier (nama_supplier, tipe_supplier, alamat, no_telepon, jenis_material, status)
            VALUES (?, ?, ?, ?, ?, ?)
        ");
        $alamat = '';
        mysqli_stmt_bind_param($stmt, "ssssss", $nama, $tipe, $alamat, $telp, $material, $status);
        mysqli_stmt_execute($stmt);

        header("Location: index.php?tipe=" . urlencode($tipe));
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
        <input type="hidden" name="tipe_supplier" value="<?= htmlspecialchars($tipe); ?>">

        <div class="mb-3">
            <label>Nama Supplier</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Tipe Supplier</label>
            <select class="form-control" disabled>
                <option><?= ucfirst(htmlspecialchars($tipe)); ?></option>
            </select>
        </div>

        <div class="mb-3">
            <label>Kontak</label>
            <input type="text" name="telepon" class="form-control">
        </div>

        <div class="mb-3">
            <label>Barang / Jasa</label>
            <input type="text" name="material" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="aktif">Aktif</option>
                <option value="tidak_aktif">Tidak Aktif</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>
        <a href="index.php?tipe=<?= urlencode($tipe); ?>" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
