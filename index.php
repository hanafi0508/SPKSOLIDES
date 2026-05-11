<?php
require_once __DIR__ . '/config/session.php';

if (isset($_SESSION['id_user'])) {
    header('Location: pages/dashboard.php');
    exit;
}

header('Location: auth/login.php');
exit;
