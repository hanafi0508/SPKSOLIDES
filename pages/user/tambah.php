<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$error = '';

if (isset($_POST['submit'])) {

    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $level = $_POST['level'];

    $stmtCek = mysqli_prepare($conn, "SELECT id_user FROM users WHERE username = ?");
    mysqli_stmt_bind_param($stmtCek, "s", $username);
    mysqli_stmt_execute($stmtCek);
    $cek = mysqli_stmt_get_result($stmtCek);

    if (mysqli_num_rows($cek) > 0) {
        $error = "Username sudah digunakan";
    } elseif (!in_array($level, ['admin', 'pimpinan'], true)) {
        $error = "Level user tidak valid";
    } else {
        $stmtInsert = mysqli_prepare($conn, "
            INSERT INTO users (nama_user, username, password, level)
            VALUES (?, ?, ?, ?)
        ");
        mysqli_stmt_bind_param($stmtInsert, "ssss", $nama, $username, $password, $level);
        mysqli_stmt_execute($stmtInsert);
        header("Location: index.php");
        exit;
    }
}
?>

<?php include '../../layouts/header.php'; ?>
<?php include '../../layouts/navbar.php'; ?>
<?php include '../../layouts/sidebar.php'; ?>

<div class="col-md-10 p-4">

    <h3>Tambah User</h3>

    <?php if ($error): ?>
        <div class="alert alert-danger"><?= $error; ?></div>
    <?php endif; ?>

    <form method="POST">

        <div class="mb-3">
            <label>Nama</label>
            <input type="text" name="nama" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Username</label>
            <input type="text" name="username" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Password</label>
            <input type="password" name="password" class="form-control" required>
        </div>

        <div class="mb-3">
            <label>Level</label>
            <select name="level" class="form-control">
                <option value="admin">Admin</option>
                <option value="pimpinan">Pimpinan</option>
            </select>
        </div>

        <button name="submit" class="btn btn-success">Simpan</button>

    </form>

</div>

<?php include '../../layouts/footer.php'; ?>
