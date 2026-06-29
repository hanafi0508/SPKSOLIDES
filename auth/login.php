<?php
require_once '../config/session.php';
require_once '../config/config.php';

if (isset($_SESSION['id_user'])) {
    header("Location: " . BASE_URL . "/pages/dashboard.php");
    exit;
}
?>


<!DOCTYPE html>
<html>
<head>
    <title>Login - SPK Supplier</title>
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <link href="<?php echo BASE_URL; ?>/assets/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link href="<?php echo BASE_URL; ?>/assets/css/custom.css" rel="stylesheet">
</head>
<body class="app-shell">

<div class="container mt-5">
    <div class="col-md-4 mx-auto">
        <div class="card shadow p-4">

            <div class="text-center mb-3">
                <div class="brand-logo mx-auto mb-2">SPK</div>
                <h4>Login</h4>
            </div>

            <?php if (isset($_GET['error'])): ?>
                <div class="alert alert-danger">
                    Username atau password salah!
                </div>
            <?php endif; ?>

            <form method="POST" action="<?php echo BASE_URL; ?>/auth/proses_login.php">

                <div class="mb-3">
                    <label>Username</label>
                    <input 
                        type="text" 
                        name="username" 
                        class="form-control" 
                        required
                    >
                </div>

                <div class="mb-3">
                    <label>Password</label>
                    <input 
                        type="password" 
                        name="password" 
                        class="form-control" 
                        required
                    >
                </div>

                <button type="submit" class="btn btn-primary w-100">
                    Login
                </button>

            </form>

        </div>
    </div>
</div>

</body>
</html>
