# DATABASE DOCUMENTATION

# Sistem Pendukung Keputusan Pemilihan Supplier

## Menggunakan Metode AHP dan F-AHP

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
| Metode        | AHP dan F-AHP                                 |
| Role User     | Admin dan Pimpinan                            |

---

## 2. Tujuan Database

Database ini digunakan untuk menyimpan seluruh data yang dibutuhkan oleh aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

Database dirancang untuk mendukung proses:

1. Autentikasi pengguna.
2. Pengelolaan data proyek.
3. Pengelolaan data supplier.
4. Pengelolaan data kriteria.
5. Pengelolaan alternatif supplier pada setiap proyek.
6. Input nilai supplier terhadap kriteria.
7. Input nilai perbandingan AHP.
8. Penyimpanan bobot hasil metode AHP.
9. Input nilai linguistik F-AHP.
10. Penyimpanan bobot hasil metode F-AHP.
11. Penyimpanan hasil ranking supplier.
12. Penyimpanan laporan hasil keputusan.

---

## 3. Daftar Tabel

Database terdiri dari tabel berikut:

|  No | Nama Tabel           | Fungsi                                                  |
| --: | -------------------- | ------------------------------------------------------- |
|   1 | `users`              | Menyimpan data pengguna sistem                          |
|   2 | `proyek`             | Menyimpan data proyek pembangunan                       |
|   3 | `supplier`           | Menyimpan data supplier                                 |
|   4 | `kriteria`           | Menyimpan data kriteria penilaian                       |
|   5 | `alternatif`         | Menyimpan supplier yang menjadi alternatif dalam proyek |
|   6 | `penilaian_supplier` | Menyimpan nilai supplier terhadap kriteria              |
|   7 | `perbandingan_ahp`   | Menyimpan nilai perbandingan berpasangan metode AHP     |
|   8 | `bobot_ahp`          | Menyimpan hasil bobot kriteria metode AHP               |
|   9 | `skala_fuzzy`        | Menyimpan skala linguistik F-AHP                        |
|  10 | `perbandingan_fahp`  | Menyimpan nilai perbandingan metode F-AHP               |
|  11 | `bobot_fahp`         | Menyimpan hasil bobot kriteria metode F-AHP             |
|  12 | `hasil_perhitungan`  | Menyimpan hasil ranking supplier                        |

---

## 4. Konsep Relasi Database

Database menggunakan konsep relasi sebagai berikut:

1. Satu proyek dapat memiliki banyak alternatif supplier.
2. Satu supplier dapat digunakan pada banyak proyek.
3. Satu proyek memiliki banyak data penilaian supplier.
4. Satu kriteria digunakan dalam banyak data penilaian supplier.
5. Satu proyek memiliki banyak nilai perbandingan AHP.
6. Satu proyek memiliki banyak nilai perbandingan F-AHP.
7. Satu proyek memiliki hasil bobot AHP.
8. Satu proyek memiliki hasil bobot F-AHP.
9. Satu proyek memiliki hasil ranking supplier.
10. Satu alternatif supplier dapat memiliki hasil ranking untuk metode AHP dan F-AHP.

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
- created_at

ALTERNATIF
- id_alternatif PK
- id_proyek FK
- id_supplier FK
- keterangan
- created_at

PENILAIAN_SUPPLIER
- id_penilaian PK
- id_proyek FK
- id_alternatif FK
- id_kriteria FK
- nilai

PERBANDINGAN_AHP
- id_perbandingan_ahp PK
- id_proyek FK
- id_kriteria_1 FK
- id_kriteria_2 FK
- nilai_perbandingan

BOBOT_AHP
- id_bobot_ahp PK
- id_proyek FK
- id_kriteria FK
- bobot
- lambda_value
- ci
- cr
- status_konsistensi
- created_at

SKALA_FUZZY
- id_skala PK
- kode
- keterangan
- nilai_l
- nilai_m
- nilai_u

PERBANDINGAN_FAHP
- id_perbandingan_fahp PK
- id_proyek FK
- id_kriteria_1 FK
- id_kriteria_2 FK
- id_skala FK
- nilai_l
- nilai_m
- nilai_u

BOBOT_FAHP
- id_bobot_fahp PK
- id_proyek FK
- id_kriteria FK
- sintesis_l
- sintesis_m
- sintesis_u
- derajat_kemungkinan
- bobot_normalisasi
- created_at

HASIL_PERHITUNGAN
- id_hasil PK
- id_proyek FK
- id_alternatif FK
- metode
- nilai_akhir
- ranking
- tanggal_hitung
```
