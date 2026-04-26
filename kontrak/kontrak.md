# KONTRAK TEKNIS PENGEMBANGAN APLIKASI

# Sistem Pendukung Keputusan Pemilihan Supplier Proyek Pembangunan

## Menggunakan Perbandingan Metode Analytical Hierarchy Process (AHP) dan Fuzzy Analytical Hierarchy Process (F-AHP)

---

## 1. Identitas Proyek

### 1.1 Nama Aplikasi

**Sistem Pendukung Keputusan Pemilihan Supplier Proyek Pembangunan**

### 1.2 Judul Penelitian

**Perancangan Sistem Pendukung Keputusan untuk Menentukan Supplier Terpilih pada Proyek Pembangunan Menggunakan Perbandingan Metode Analytical Hierarchy Process (AHP) dan Fuzzy Analytical Hierarchy Process (F-AHP) pada PT Solides Propertindo**

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
| Metode SPK        | AHP dan F-AHP      |

---

## 2. Tujuan Dokumen

Dokumen ini dibuat sebagai acuan utama dalam proses perancangan dan pengembangan aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

Dokumen ini bertujuan untuk:

1. Menjaga arah pengembangan aplikasi agar tetap konsisten.
2. Menentukan ruang lingkup aplikasi secara jelas.
3. Menetapkan fitur-fitur yang harus dibangun.
4. Menentukan struktur database yang digunakan.
5. Menetapkan alur kerja sistem.
6. Menentukan standar tampilan antarmuka.
7. Menentukan standar penulisan kode.
8. Menentukan metode perhitungan AHP dan F-AHP.
9. Menentukan batasan pengembangan aplikasi.
10. Menjadi pedoman kerja dari tahap awal sampai aplikasi selesai.

---

## 3. Pernyataan Komitmen Pengembangan

Aplikasi ini akan dikembangkan secara bertahap, terstruktur, dan konsisten berdasarkan dokumen ini.

Setiap perubahan besar terhadap struktur sistem, database, metode perhitungan, atau fitur utama harus dicatat pada bagian **Catatan Perubahan** agar pengembangan tetap terdokumentasi.

Dokumen ini menjadi acuan utama selama pembangunan aplikasi, sehingga setiap fitur yang dibuat harus sesuai dengan rancangan yang telah ditentukan.

---

## 4. Ruang Lingkup Aplikasi

### 4.1 Fitur yang Termasuk dalam Pengembangan

Aplikasi ini mencakup fitur-fitur berikut:

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
13. Input nilai linguistik metode F-AHP.
14. Konversi nilai linguistik ke Triangular Fuzzy Number.
15. Proses perhitungan metode F-AHP.
16. Perhitungan nilai akhir supplier.
17. Ranking supplier berdasarkan metode AHP.
18. Ranking supplier berdasarkan metode F-AHP.
19. Perbandingan hasil metode AHP dan F-AHP.
20. Laporan hasil keputusan.
21. Cetak laporan.
22. Logout pengguna.

### 4.2 Fitur yang Tidak Termasuk dalam Versi Awal

Untuk menjaga fokus pengembangan, fitur berikut tidak wajib dibuat pada versi awal:

1. Export PDF otomatis menggunakan library eksternal.
2. Grafik interaktif menggunakan Chart.js.
3. Multi perusahaan.
4. API eksternal.
5. Notifikasi email.
6. Upload dokumen supplier.
7. Audit log detail.
8. Subkriteria bertingkat.
9. Multi decision maker.
10. Integrasi WhatsApp atau email.

Fitur tersebut dapat ditambahkan pada versi lanjutan apabila aplikasi utama sudah selesai dan stabil.

---

## 5. Batasan Pengembangan

Aplikasi ini memiliki batasan sebagai berikut:

1. Aplikasi dibangun menggunakan PHP Native tanpa framework backend seperti Laravel.
2. Tampilan menggunakan Bootstrap.
3. Database menggunakan MySQL.
4. Sistem hanya memiliki dua role utama, yaitu **Admin** dan **Pimpinan**.
5. Proses perhitungan dilakukan berdasarkan data yang dimasukkan oleh Admin.
6. Pimpinan hanya dapat melihat hasil dan laporan.
7. Metode yang digunakan hanya AHP dan F-AHP.
8. Kriteria yang digunakan pada versi awal adalah kriteria utama, bukan subkriteria.
9. Sistem hanya menghitung ranking supplier berdasarkan satu proyek yang dipilih.
10. Data supplier dan kriteria dapat dikelola secara dinamis melalui sistem.

---

## 6. Role dan Hak Akses Pengguna

Aplikasi ini hanya menggunakan dua role:

1. Admin
2. Pimpinan

---

## 6.1 Admin

Admin merupakan pengguna utama yang memiliki hak akses penuh terhadap sistem.

### Hak Akses Admin

|  No | Fitur                      | Akses |
| --: | -------------------------- | ----- |
|   1 | Login                      | Ya    |
|   2 | Dashboard                  | Ya    |
|   3 | Kelola User                | Ya    |
|   4 | Kelola Proyek              | Ya    |
|   5 | Kelola Supplier            | Ya    |
|   6 | Kelola Kriteria            | Ya    |
|   7 | Kelola Alternatif Supplier | Ya    |
|   8 | Input Penilaian Supplier   | Ya    |
|   9 | Input Perbandingan AHP     | Ya    |
|  10 | Proses Perhitungan AHP     | Ya    |
|  11 | Input Perbandingan F-AHP   | Ya    |
|  12 | Proses Perhitungan F-AHP   | Ya    |
|  13 | Lihat Hasil Ranking        | Ya    |
|  14 | Lihat Perbandingan Metode  | Ya    |
|  15 | Cetak Laporan              | Ya    |
|  16 | Logout                     | Ya    |

### Tanggung Jawab Admin

Admin bertanggung jawab untuk:

1. Mengelola data master.
2. Mengelola data proyek.
3. Mengelola data supplier.
4. Mengelola data kriteria.
5. Menentukan alternatif supplier untuk setiap proyek.
6. Menginput nilai supplier.
7. Menginput nilai perbandingan AHP.
8. Menginput nilai linguistik F-AHP.
9. Menjalankan proses perhitungan.
10. Memastikan hasil perhitungan tersimpan.
11. Menyediakan laporan untuk pimpinan.

---

## 6.2 Pimpinan

Pimpinan merupakan pengguna yang hanya memiliki akses untuk melihat hasil akhir dari sistem.

### Hak Akses Pimpinan

|  No | Fitur                     | Akses |
| --: | ------------------------- | ----- |
|   1 | Login                     | Ya    |
|   2 | Dashboard                 | Ya    |
|   3 | Lihat Hasil Ranking       | Ya    |
|   4 | Lihat Detail Perhitungan  | Ya    |
|   5 | Lihat Perbandingan Metode | Ya    |
|   6 | Cetak Laporan             | Ya    |
|   7 | Kelola Data Master        | Tidak |
|   8 | Input Nilai               | Tidak |
|   9 | Proses Perhitungan        | Tidak |
|  10 | Logout                    | Ya    |

### Tanggung Jawab Pimpinan

Pimpinan bertanggung jawab untuk:

1. Melihat hasil ranking supplier.
2. Melihat perbandingan metode AHP dan F-AHP.
3. Melihat rekomendasi supplier terbaik.
4. Mencetak laporan hasil keputusan.
5. Mengambil keputusan akhir berdasarkan hasil sistem.

---

## 7. Alur Sistem Secara Umum

Alur utama aplikasi adalah sebagai berikut:

```text
Mulai
↓
User membuka aplikasi
↓
User login
↓
Sistem memvalidasi username dan password
↓
Jika login gagal, sistem menampilkan pesan error
↓
Jika login berhasil, sistem memeriksa role user
↓
Jika role Admin, sistem menampilkan menu lengkap
↓
Jika role Pimpinan, sistem menampilkan menu terbatas
↓
Admin mengelola data proyek, supplier, kriteria, dan alternatif
↓
Admin menginput nilai supplier terhadap kriteria
↓
Admin menginput nilai perbandingan AHP
↓
Sistem menghitung bobot AHP dan Consistency Ratio
↓
Jika CR > 0,10, sistem meminta perbaikan nilai perbandingan
↓
Jika CR <= 0,10, sistem menyimpan bobot AHP
↓
Admin menginput nilai linguistik F-AHP
↓
Sistem mengonversi nilai linguistik ke Triangular Fuzzy Number
↓
Sistem menghitung bobot F-AHP
↓
Sistem menghitung nilai akhir supplier
↓
Sistem menampilkan ranking AHP dan F-AHP
↓
Sistem menampilkan perbandingan hasil metode
↓
Pimpinan melihat hasil ranking dan laporan
↓
Selesai
```
