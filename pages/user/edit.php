<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$stmt = mysqli_prepare($conn, "SELECT * FROM users WHERE id_user = ?");
mysqli_stmt_bind_param($stmt, "i", $id);
mysqli_stmt_execute($stmt);
$data = mysqli_stmt_get_result($stmt);
$row = mysqli_fetch_assoc($data);
$error = '';

if (!$row) {
    header("Location: index.php");
    exit;
}

if (isset($_POST['submit'])) {

    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $level = $_POST['level'];

    $stmtCek = mysqli_prepare($conn, "SELECT id_user FROM users WHERE username = ? AND id_user != ?");
    mysqli_stmt_bind_param($stmtCek, "si", $username, $id);
    mysqli_stmt_execute($stmtCek);
    $cek = mysqli_stmt_get_result($stmtCek);

    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan";
    } elseif (!in_array($level, ['admin', 'pimpinan'], true)) {
        $error = "Level user tidak valid";
    } elseif (!empty($_POST['password'])) {
        $password = $_POST['password'];
        $stmtUpdate = mysqli_prepare($conn, "
            UPDATE users
            SET nama_user = ?, username = ?, password = ?, level = ?
            WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmtUpdate, "ssssi", $nama, $username, $password, $level, $id);
        mysqli_stmt_execute($stmtUpdate);
        header("Location: index.php");
        exit;
    } else {
        $stmtUpdate = mysqli_prepare($conn, "
            UPDATE users
            SET nama_user = ?, username = ?, level = ?
            WHERE id_user = ?
        ");
        mysqli_stmt_bind_param($stmtUpdate, "sssi", $nama, $username, $level, $id);
        mysqli_stmt_execute($stmtUpdate);
        header("Location: index.php");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Edit User</h3>

    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" value="<?= $row['nama_user']; ?>">
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" value="<?= $row['username']; ?>">
        </div>

        <div class="mb-3">
            <label>Password (kosongkan jika tidak diubah)</label>
            <input type="password" name="password" class="form-control">
        </div>

        <div class="mb-3">
            <label>Level</label>
            <select name="level" class="form-control">
                <option value="admin" <?= $row['level']=='admin'?'selected':''; ?>>Admin</option>
                <option value="pimpinan" <?= $row['level']=='pimpinan'?'selected':''; ?>>Pimpinan</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Update</button>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
