<?php
require_once '../../config/session.php';
require_once '../../config/database.php';
require_once '../../functions/auth_function.php';

check_login();
check_admin();

$id_proyek = (int)$_POST['id_proyek'];

foreach ($_POST['nilai'] as $id_alt => $k) {
    foreach ($k as $id_krit => $val) {

        if ($val === '' || !is_numeric($val)) {
            header("Location: index.php?proyek=$id_proyek&error=1");
            exit;
        }

        $id_alt = (int)$id_alt;
        $id_krit = (int)$id_krit;
        $val = (float)$val;

        $cek = mysqli_query($conn, "
            SELECT id_penilaian FROM penilaian_supplier 
            WHERE id_proyek=$id_proyek 
            AND id_alternatif=$id_alt 
            AND id_kriteria=$id_krit
        ");

        if (mysqli_num_rows($cek) > 0) {
            mysqli_query($conn, "
                UPDATE penilaian_supplier 
                SET nilai=$val
                WHERE id_proyek=$id_proyek
                AND id_alternatif=$id_alt
                AND id_kriteria=$id_krit
            ");
        } else {
            mysqli_query($conn, "
                INSERT INTO penilaian_supplier 
                (id_proyek, id_alternatif, id_kriteria, nilai)
                VALUES ($id_proyek,$id_alt,$id_krit,$val)
            ");
        }
    }
}

header("Location: index.php?proyek=$id_proyek&success=1");
exit;