# KONTRAK TEKNIS FRONTEND DAN BACKEND

# Sistem Pendukung Keputusan Pemilihan Supplier

## Menggunakan PHP Native, Bootstrap, dan MySQL

### PT Solides Propertindo

---

## 1. Identitas Dokumen

| Informasi          | Keterangan                                    |
| ------------------ | --------------------------------------------- |
| Nama Dokumen       | frontend-backend.md                           |
| Jenis Dokumen      | Kontrak Teknis Frontend dan Backend           |
| Aplikasi           | Sistem Pendukung Keputusan Pemilihan Supplier |
| Teknologi Backend  | PHP Native                                    |
| Teknologi Frontend | HTML, CSS, JavaScript, Bootstrap              |
| Database           | MySQL                                         |
| Metode             | AHP dan F-AHP                                 |
| Role User          | Admin dan Pimpinan                            |
| Status             | Draft Final Awal                              |
| Versi              | 1.0                                           |

---

## 2. Tujuan Dokumen

Dokumen ini dibuat sebagai pedoman teknis dalam membangun bagian **frontend** dan **backend** aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

Dokumen ini bertujuan untuk:

1. Menentukan standar struktur halaman aplikasi.
2. Menentukan standar tampilan antarmuka menggunakan Bootstrap.
3. Menentukan struktur folder frontend dan backend.
4. Menentukan standar pembuatan layout.
5. Menentukan standar validasi form.
6. Menentukan standar proses CRUD.
7. Menentukan standar autentikasi dan session.
8. Menentukan aturan hak akses role Admin dan Pimpinan.
9. Menentukan aturan integrasi PHP dengan MySQL.
10. Menjaga agar pengembangan aplikasi tetap rapi, konsisten, dan mudah diuji.

---

## 3. Ruang Lingkup Frontend dan Backend

Dokumen ini mencakup:

1. Struktur folder aplikasi.
2. Layout utama aplikasi.
3. Standar tampilan halaman.
4. Standar komponen Bootstrap.
5. Standar navigasi menu.
6. Standar halaman Admin.
7. Standar halaman Pimpinan.
8. Standar proses backend PHP.
9. Standar koneksi database.
10. Standar query database.
11. Standar validasi form.
12. Standar keamanan aplikasi.
13. Standar pesan berhasil dan gagal.
14. Standar proses CRUD.
15. Standar halaman perhitungan AHP dan F-AHP.
16. Standar halaman ranking dan laporan.

---

## 4. Teknologi yang Digunakan

| Bagian             | Teknologi                         |
| ------------------ | --------------------------------- |
| Backend            | PHP Native                        |
| Frontend           | HTML, CSS                         |
| UI Framework       | Bootstrap                         |
| Icon               | Bootstrap Icons atau Font Awesome |
| Database           | MySQL                             |
| Web Server         | Apache                            |
| Development Server | XAMPP atau Laragon                |
| Browser Utama      | Google Chrome                     |
| Editor             | Visual Studio Code                |

---

## 5. Prinsip Utama Pengembangan

Pengembangan frontend dan backend wajib mengikuti prinsip berikut:

1. Sederhana tetapi rapi.
2. Konsisten dalam struktur folder.
3. Konsisten dalam nama file.
4. Konsisten dalam nama variabel.
5. Menggunakan Bootstrap untuk tampilan.
6. Menggunakan PHP Native tanpa framework.
7. Menggunakan MySQL sebagai database.
8. Menggunakan session untuk login.
9. Menggunakan role untuk pembatasan akses.
10. Menggunakan validasi input pada setiap form.
11. Menggunakan prepared statement pada proses penting.
12. Setiap halaman harus mudah dipahami dan diuji.
13. Setiap fitur dibuat modular agar tidak membingungkan.
14. Tampilan Admin dan Pimpinan harus dibedakan berdasarkan hak akses.
