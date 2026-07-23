<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$tipeRedirect = $_GET['tipe'] ?? 'barang';

if (!in_array($tipeRedirect, ['barang', 'jasa'], true)) {
    $tipeRedirect = 'barang';
}

$stmt = mysqli_prepare($conn, "SELECT * FROM supplier WHERE id_supplier = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$row = mysqli_fetch_assoc(mysqli_stmt_get_result($stmt));

$error = '';

if (!$row) {
    header("Location: index.php?tipe=" . urlencode($tipeRedirect));
    exit;
}

if (isset($_POST['submit'])) {
    $nama = trim($_POST['nama']);
    $tipe = $_POST['tipe_supplier'] ?? 'barang';
    $telp = trim($_POST['telepon']);
    $material = trim($_POST['material']);
    $status = $_POST['status'];

    if (empty($nama)) {
        $error = "Nama supplier wajib diisi";
    } elseif (!in_array($tipe, ['barang', 'jasa'], true)) {
        $error = "Tipe supplier tidak valid";
    } elseif (empty($material)) {
        $error = "Barang / jasa wajib diisi";
    } else {
        $stmtUpdate = mysqli_prepare($conn, "
            UPDATE supplier SET
                nama_supplier = ?,
                tipe_supplier = ?,
                alamat = ?,
                no_telepon = ?,
                jenis_material = ?,
                status = ?
            WHERE id_supplier = ?
        ");
        $alamat = '';
        mysqli_stmt_bind_param($stmtUpdate, "ssssssi", $nama, $tipe, $alamat, $telp, $material, $status, $id);
        mysqli_stmt_execute($stmtUpdate);

        header("Location: index.php?tipe=" . urlencode($tipe));
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
        <div class="alert alert-danger"><?= htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama Supplier</label>
            <input type="text" name="nama" class="form-control" value="<?= htmlspecialchars($row['nama_supplier'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Tipe Supplier</label>
            <select name="tipe_supplier" class="form-control">
                <option value="barang" <?= ($row['tipe_supplier'] ?? 'barang') === 'barang' ? 'selected' : ''; ?>>Barang</option>
                <option value="jasa" <?= ($row['tipe_supplier'] ?? '') === 'jasa' ? 'selected' : ''; ?>>Jasa</option>
            </select>
        </div>

        <div class="mb-3">
            <label>Kontak</label>
            <input type="text" name="telepon" class="form-control" value="<?= htmlspecialchars($row['no_telepon'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Barang / Jasa</label>
            <input type="text" name="material" class="form-control" value="<?= htmlspecialchars($row['jenis_material'] ?? ''); ?>">
        </div>

        <div class="mb-3">
            <label>Status</label>
            <select name="status" class="form-control">
                <option value="aktif" <?= $row['status'] == 'aktif' ? 'selected' : ''; ?>>Aktif</option>
                <option value="tidak_aktif" <?= $row['status'] == 'tidak_aktif' ? 'selected' : ''; ?>>Tidak Aktif</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Update</button>
        <a href="index.php?tipe=<?= urlencode($tipeRedirect); ?>" class="btn btn-secondary">Kembali</a>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
