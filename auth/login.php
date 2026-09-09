<?php
require_once '../config/session.php';
require_once '../config/config.php';
require_once '../config/database.php';
require_once '../functions/auth_function.php';

if (isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;
}

$lockedRemaining = 0;
$lockedUsername = $_SESSION['last_login_user'] ?? ($_GET['username'] ?? '');

if ($lockedUsername !== '') {
    $check = login_is_blocked($conn, $lockedUsername, login_client_ip());
    if ($check['blocked']) {
        $lockedRemaining = (int) ceil($check['remaining'] / 60);
    }
}

if (isset($_GET['locked']) && $lockedRemaining === 0) {
    $wait = max(1, (int) ($_GET['wait'] ?? 1));
    $lockedRemaining = $wait;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>SOLIDES</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/custom.css" rel="stylesheet">
</head>
<body class="login-shell">

<div class="container">
    <div class="col-md-5 col-lg-4 mx-auto">
        <div class="card login-card shadow p-4">

            <div class="text-center mb-3">
                <img src="<?php echo BASE_URL; ?>/assets/logo-solides.png" alt="SOLIDES" class="brand-logo-img mb-2">
                <h4 class="login-title fw-bold">SOLIDES</h4>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Username atau password salah!
                </div>
            <?php endif; ?>

            <?php if ($lockedRemaining > 0): ?>
                <div class="alert alert-warning">
                    Terlalu banyak percobaan login gagal. Akun terkunci sementara, coba lagi dalam ±<?= $lockedRemaining; ?> menit.
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/proses_login.php">

                <?php csrf_field(); ?>

                <div class="mb-3">
                    <label>Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        placeholder="Masukkan username"
                        <?= $lockedRemaining > 0 ? 'disabled' : ''; ?>
                        required
                    >
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        placeholder="Masukkan password"
                        <?= $lockedRemaining > 0 ? 'disabled' : ''; ?>
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100" <?= $lockedRemaining > 0 ? 'disabled' : ''; ?>>
                    Masuk
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
