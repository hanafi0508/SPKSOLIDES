<?php
require_once 'config/database.php';
echo "<h2>Test Koneksi Database</h2>";
if ($conn) {
    echo "<p style='color:green;'>Koneksi berhasil!</p>";
} else {
    echo "<p style='color:red;'>Koneksi gagal!</p>";
}
$query = mysqli_query($conn, "SELECT DATABASE() as db");

$data = mysqli_fetch_assoc($query);

echo "<p>Database aktif: <b>" . $data['db'] . "</b></p>";

$result = mysqli_query($conn, "SELECT * FROM kriteria");

echo "<h3>Kriteria:</h3>";

while ($row = mysqli_fetch_assoc($result)) {
    echo $row['nama_kriteria'] . " - " . $row['jenis_kriteria'] . "<br>";
}