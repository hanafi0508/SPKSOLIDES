<?php
require_once '../../functions/init.php';
require_auth('admin');
verify_csrf();

$error = '';

if (isset($_POST['submit'])) {

    $nama = trim($_POST['nama']);
    $username = trim($_POST['username']);
    $password = $_POST['password'];
    $level = $_POST['level'];

    if (db_one($conn, 'SELECT id_user FROM users WHERE username = ?', 's', [$username])) {
        $error = 'Username sudah digunakan';
    } elseif (!in_array($level, ['admin', 'pimpinan'], true)) {
        $error = 'Level user tidak valid';
    } elseif (strlen($password) < 1) {
        $error = 'Password wajib diisi';
    } else {
        $hash = hash_user_password($password);
        db_exec($conn, 'INSERT INTO users (nama_user, username, password, level) VALUES (?, ?, ?, ?)', 'ssss', [$nama, $username, $hash, $level]);
        header('Location: index.php');
        exit;
    }
}

layout_top();
?>
<h3>Tambah User</h3>

<?php if ($error): ?>
    <div class="alert alert-danger"><?= h($error); ?></div>
<?php endif; ?>

<form method="POST">
    <?php csrf_field(); ?>

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
<?php layout_bottom(); ?>