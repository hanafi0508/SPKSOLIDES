# KONTRAK TEKNIS PENGEMBANGAN APLIKASI

# Sistem Pendukung Keputusan Pemilihan Supplier Proyek Pembangunan

## Menggunakan Metode Analytical Hierarchy Process (AHP)

---

## 1. Identitas Proyek

### 1.1 Nama Aplikasi

**Sistem Pendukung Keputusan Pemilihan Supplier Proyek Pembangunan**

### 1.2 Judul Penelitian

**Perancangan Sistem Pendukung Keputusan untuk Menentukan Supplier Terpilih pada Proyek Pembangunan Menggunakan Metode Analytical Hierarchy Process (AHP) pada PT Solides Propertindo**

### 1.3 Jenis Aplikasi

Aplikasi berbasis web.

### 1.4 Teknologi Utama

| Komponen          | Teknologi          |
| ----------------- | ------------------ |
| Backend           | PHP Native         |
| Frontend          | HTML, CSS          |
| Framework UI      | Bootstrap          |
| Database          | MySQL              |
| Web Server        | Apache             |
| Local Development | XAMPP / Laragon    |
| Bahasa Database   | SQL                |
| Arsitektur        | Modular PHP Native |
| Metode SPK        | AHP                |

---

## 2. Ruang Lingkup Aplikasi

Aplikasi mencakup fitur berikut:

1. Login pengguna.
2. Hak akses pengguna berdasarkan role.
3. Dashboard.
4. Manajemen data user.
5. Manajemen data proyek.
6. Manajemen data supplier.
7. Manajemen data kriteria.
8. Manajemen data alternatif supplier.
9. Input nilai supplier terhadap kriteria.
10. Input nilai perbandingan metode AHP.
11. Proses perhitungan metode AHP.
12. Uji konsistensi metode AHP.
13. Perhitungan nilai akhir supplier.
14. Ranking supplier berdasarkan metode AHP.
15. Laporan hasil keputusan.
16. Cetak laporan.
17. Logout pengguna.

---

## 3. Batasan Pengembangan

1. Aplikasi dibangun menggunakan PHP Native.
2. Tampilan menggunakan Bootstrap.
3. Database menggunakan MySQL.
4. Sistem memiliki role Admin dan Pimpinan.
5. Proses perhitungan dilakukan berdasarkan data yang dimasukkan Admin.
6. Pimpinan hanya dapat melihat hasil dan laporan.
7. Metode yang digunakan adalah AHP.
8. Kriteria yang digunakan adalah kriteria utama.
9. Sistem menghitung ranking supplier berdasarkan satu proyek yang dipilih.

---

## 4. Role dan Hak Akses

### Admin

Admin memiliki akses untuk mengelola data master, input penilaian, input perbandingan AHP, proses perhitungan, melihat ranking, dan mencetak laporan.

### Pimpinan

Pimpinan memiliki akses untuk melihat dashboard, ranking, detail perhitungan, laporan, dan mencetak laporan.

---

## 5. Alur Sistem

```text
Login
Pilih Proyek
Kelola Supplier dan Kriteria
Tentukan Alternatif Supplier
Input Penilaian Supplier
Input Perbandingan AHP
Proses Perhitungan AHP
Uji Konsistensi
Hitung Ranking Supplier
Tampilkan Rekomendasi
Cetak Laporan
```
