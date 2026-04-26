<?php

function check_login() {
    if (!isset($_SESSION['id_user'])) {
        header("Location: ../auth/login.php");
        exit;
    }
}

function check_admin() {
    if ($_SESSION['level'] !== 'admin') {
        echo "Akses ditolak";
        exit;
    }
}

function check_pimpinan() {
    if ($_SESSION['level'] !== 'pimpinan') {
        echo "Akses ditolak";
        exit;
    }
}