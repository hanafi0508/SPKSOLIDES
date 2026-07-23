# DATABASE DOCUMENTATION

# Sistem Pendukung Keputusan Pemilihan Supplier

## Menggunakan Metode AHP

### PT Solides Propertindo

---

## 1. Identitas Database

| Informasi     | Keterangan                                    |
| ------------- | --------------------------------------------- |
| Nama Database | `spk_supplier`                                |
| DBMS          | MySQL                                         |
| Engine        | InnoDB                                        |
| Charset       | utf8mb4                                       |
| Collation     | utf8mb4_general_ci                            |
| Aplikasi      | Sistem Pendukung Keputusan Pemilihan Supplier |
| Backend       | PHP Native                                    |
| Frontend      | Bootstrap                                     |
| Metode        | AHP                                           |
| Role User     | Admin dan Pimpinan                            |

---

## 2. Tujuan Database

Database ini digunakan untuk menyimpan seluruh data yang dibutuhkan oleh aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

Database mendukung proses:

1. Autentikasi pengguna.
2. Pengelolaan data proyek.
3. Pengelolaan data supplier.
4. Pengelolaan data kriteria.
5. Pengelolaan alternatif supplier pada setiap proyek.
6. Input nilai supplier terhadap kriteria.
7. Input nilai perbandingan AHP.
8. Penyimpanan bobot hasil metode AHP.
9. Penyimpanan hasil ranking supplier.
10. Penyimpanan laporan hasil keputusan.

---

## 3. Daftar Tabel

| No | Nama Tabel           | Fungsi                                                  |
| -- | -------------------- | ------------------------------------------------------- |
| 1  | `users`              | Menyimpan data pengguna sistem                          |
| 2  | `proyek`             | Menyimpan data proyek pembangunan                       |
| 3  | `supplier`           | Menyimpan data supplier                                 |
| 4  | `kriteria`           | Menyimpan data kriteria penilaian                       |
| 5  | `alternatif`         | Menyimpan supplier yang menjadi alternatif dalam proyek |
| 6  | `penilaian_supplier` | Menyimpan nilai supplier terhadap kriteria              |
| 7  | `perbandingan_ahp`   | Menyimpan nilai perbandingan berpasangan metode AHP     |
| 8  | `bobot_ahp`          | Menyimpan hasil bobot kriteria metode AHP               |
| 9  | `hasil_perhitungan`  | Menyimpan hasil ranking supplier                        |

---

## 4. Konsep Relasi Database

1. Satu proyek dapat memiliki banyak alternatif supplier.
2. Satu supplier dapat digunakan pada banyak proyek.
3. Satu proyek memiliki banyak data penilaian supplier.
4. Satu kriteria digunakan dalam banyak data penilaian supplier.
5. Satu proyek memiliki banyak nilai perbandingan AHP.
6. Satu proyek memiliki hasil bobot AHP.
7. Satu alternatif supplier dapat memiliki hasil ranking AHP.

---

## 5. ERD Tekstual

```text
USERS
- id_user PK
- nama_user
- username
- password
- level
- created_at

PROYEK
- id_proyek PK
- nama_proyek
- lokasi
- tanggal_mulai
- tanggal_selesai
- keterangan
- created_at

SUPPLIER
- id_supplier PK
- nama_supplier
- tipe_supplier
- alamat
- no_telepon
- email
- jenis_material
- status
- created_at

KRITERIA
- id_kriteria PK
- kode_kriteria
- nama_kriteria
- jenis_kriteria
- deskripsi

ALTERNATIF
- id_alternatif PK
- id_proyek FK
- id_supplier FK

PENILAIAN_SUPPLIER
- id_penilaian PK
- id_proyek FK
- id_alternatif FK
- id_kriteria FK
- nilai

PERBANDINGAN_AHP
- id PK
- id_proyek FK
- id_kriteria_1 FK
- id_kriteria_2 FK
- nilai

BOBOT_AHP
- id PK
- id_proyek FK
- id_kriteria FK
- bobot
- lambda_value
- ci
- cr
- status_konsistensi
- created_at

HASIL_PERHITUNGAN
- id PK
- id_proyek FK
- id_alternatif FK
- metode
- nilai
- ranking
```
