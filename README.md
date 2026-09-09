# SOLIDES — Sistem Pendukung Keputusan Pemilihan Supplier dengan Metode AHP

Aplikasi berbasis web untuk **memilih supplier terbaik perusahaan konstruksi** menggunakan metode
**Analytic Hierarchy Process (AHP)**. Penilaian supplier dilakukan dengan skor sederhana **1–9**,
sedangkan bobot kepentingan kriteria dihitung otomatis dari perbandingan berpasangan (pairwise
comparison) AHP yang konsisten.

---

## 1. Latar Belakang

Pemilihan supplier merupakan keputusan strategis yang memengaruhi biaya, mutu, dan kelancaran
proyek. Proses seleksi yang hanya mengandalkan harga atau intuisi sering menghasilkan keputusan
yang tidak objektif. Permasalahan ini dapat diselesaikan dengan pendekatan *Multi-Criteria Decision
Making* (MCDM), salah satunya **AHP** yang dikembangkan oleh **Thomas L. Saaty (1980)**.

AHP memecah keputusan menjadi hierarki: **tujuan → kriteria → alternatif (supplier)**. Kriteria
dibobotkan melalui perbandingan berpasangan dan diuji konsistensinya. Setiap supplier dinilai pada
setiap kriteria, lalu dihitung nilai akhirnya:

```
Nilai Akhir Supplier = Σ (Bobot Kriteria × Skor Supplier)
```

Supplier dengan nilai akhir tertinggi menjadi **rekomendasi utama**.

---

## 2. Fitur

| Modul | Fungsi |
|---|---|
| **Login** | Autentikasi dua level: `admin` (kelola penuh) dan `pimpinan` (lihat hasil) |
| **Dashboard** | Statistik (proyek, supplier, kriteria, ranking), podium 3 besar, tabel rekomendasi & skor |
| **Kriteria** | CRUD kriteria (kode, nama, deskripsi) |
| **Bobot Kriteria (AHP)** | Input perbandingan berpasangan, hitung bobot, uji konsistensi (CR ≤ 0,1) |
| **Supplier** | CRUD supplier (identitas, kontak, jenis material, status aktif/tidak) |
| **Proyek** | CRUD proyek + relasi **proyek ↔ supplier** |
| **Penilaian Supplier** | Input skor **1–9** tiap supplier per kriteria; supplier yang belum dinilai otomatis tampil teratas; simpan + hitung ranking **tanpa pindah halaman** (AJAX) |
| **Laporan (Cetak)** | Rekap ranking & skor siap cetak |

---

## 3. Teknologi

| Lapisan | Teknologi |
|---|---|
| Backend | **PHP 7+ (native, tanpa framework)**, MySQLi prepared statements |
| Database | **MySQL** (`spk_supplier`) |
| Frontend | **Bootstrap 5** (lokal), **Bootstrap Icons** (lokal), CSS kustom, JavaScript (fetch/AJAX) |
| Keamanan | Password `password_hash()`, **CSRF token** per sesi, validasi `1–9`, transaksi DB |

---

## 4. Arsitektur Aplikasi

Layering sederhana agar mudah dipahami dan dikembangkan:

```
pages/                    → Controller + view (menerima input, merender halaman)
functions/ranking_repository.php → Akses data (SELECT/INSERT/DELETE, transaksi)
functions/ahp_function.php       → Perhitungan AHP murni (matriks, bobot, konsistensi)
functions/ranking_function.php   → Orchestrasi (hitung skor akhir, laporan)
functions/auth_function.php      → Login/session, CSRF, flash + redirect alert
functions/init.php               → Bootstrap aplikasi (session, config, DB, layout)
layouts/                        → Header, sidebar, footer (kontainer toast + modal konfirmasi)
config/                         → config.php (BASE_URL), database.php, session.php
```

- Semua halaman admin melewati `require_auth('admin')`; dashboard & cetak cukup `check_login()` (admin + pimpinan).
- Setiap POST divalidasi `verify_csrf()`; penyimpanan ranking memakai **transaksi** (`DELETE` + `INSERT` ulang).
- Pemberitahuan memakai **toast** tunggal (`showToast()` di `assets/js/custom.js`) — flash server maupun respons AJAX memakai jalur yang sama.

---

## 5. Metode AHP

### 5.1 Skala Penilaian (Saaty)

| Skala | Makna |
|---|---|
| 1 | Sama penting |
| 3 | Sedikit lebih penting |
| 5 | Lebih penting |
| 7 | Sangat lebih penting |
| 9 | Mutlak lebih penting |
| 2, 4, 6, 8 | Nilai antara |

Passing nilai sebaliknya berupa **kebalikan** (resiprokal): jika `A vs B = 3`, maka `B vs A = 1/3`.

### 5.2 Langkah Perhitungan

1. **Matriks perbandingan berpasangan** `A` ukuran `n×n` (diagonal = 1, elemen bawah = 1/element atas).
2. **Normalisasi** tiap kolom: `aᵢⱼ / Σ kolom` → matriks normalisasi `N`.
3. **Bobot prioritas**: rata-rata tiap baris `N`.
4. **Vektor jumlah terbobot (Weighted Sum Vector)**: `WSV = A × bobot`.
5. **Konsistensi**:
   - `λmax = rata-rata(WSV / bobot)`
   - `CI = (λmax − n) / (n − 1)`
   - `CR = CI / RI`  dengan RI tabel Saaty:

| n | 1 | 2 | 3 | 4 | 5 | 6 | 7 | 8 | 9 | 10 |
|---|---|---|---|---|---|---|---|---|---|---|
| RI | 0,00 | 0,00 | 0,58 | 0,90 | 1,12 | 1,24 | 1,32 | 1,41 | 1,45 | 1,49 |

6. **Konsisten** jika `CR ≤ 0,1`; jika tidak, perbandingan harus diperbaiki.

### 5.3 Penilaian Total Supplier

Skor supplier **1–9** (1 = sangat kurang, 5 = cukup, 9 = sangat baik), lalu:

```
Nilai Akhir = Σ ₖ (bobotₖ × skorₖ)
```

---

## 6. Struktur Database (`spk_supplier`)

| Tabel | Fungsi |
|---|---|
| `users` | Akun login (`admin`, `pimpinan`); password di-hash |
| `kriteria` | Kriteria penilaian (kode, nama, deskripsi) |
| `bobot_ahp` | Bobot hasil hitung AHP per kriteria (`bobot`, `lambda_value`, `ci`, `cr`, `status_konsistensi`) |
| `perbandingan_ahp` | Nilai perbandingan berpasangan antar kriteria |
| `supplier` | Data supplier (kontak, jenis material, status) |
| `proyek` | Data proyek |
| `proyek_supplier` | Relasi banyak-ke-banyak proyek ↔ supplier |
| `penilaian_supplier` | Skor 1–9 tiap (supplier × kriteria) |
| `hasil_ranking` | Hasil akhir: `id_supplier`, `nilai`, `ranking` |

Relasi utama:

```
kriteria 1─∞ bobot_ahp ;  kriteria ∞─∞ perbandingan_ahp
supplier 1─∞ penilaian_supplier ∞─1 kriteria
supplier ∞─∞ proyek (via proyek_supplier)
hasil_ranking.id_supplier → supplier.id_supplier (unik)
```

---

## 7. Data Uji (Kondisi Saat Ini)

> Data ini merupakan hasil pengujian sistem dan dapat dijadikan acuan contoh pada skripsi.

### 7.1 Kriteria & Bobot AHP (n = 5, **CR = 0,045751 → konsisten**)

| Kode | Kriteria | Bobot | % |
|---|---|---|---|
| C1 | Kriteria 1 | 0,532962 | 53,30% |
| C2 | Kriteria 2 | 0,259994 | 26,00% |
| C3 | Kriteria 3 | 0,122922 | 12,29% |
| C4 | Kriteria 4 | 0,056393 | 5,64% |
| C5 | Kriteria 5 | 0,027730 | 2,77% |

Hasil perhitungan: `λmax = 5,204964`, `CI = 0,051241`, `CR = 0,045751`.

### 7.2 Matriks Perbandingan Berpasangan (nilai kiri atas)

| | C1 | C2 | C3 | C4 | C5 |
|---|---|---|---|---|---|
| C1 | 1 | 3 | 6 | 9 | 12 |
| C2 | 1/3 | 1 | 3 | 6 | 9 |
| C3 | 1/6 | 1/3 | 1 | 3 | 6 |
| C4 | 1/9 | 1/6 | 1/3 | 1 | 3 |
| C5 | 1/12 | 1/9 | 1/6 | 1/3 | 1 |

### 7.3 Supplier Aktif (20 supplier)

`Supplier 2` … `Supplier 20` dengan jenis material sesuai nomor urut (mis. `Supplier 2` = Material 2),
plus 1 data uji tambahan. Semua berstatus **aktif**.

### 7.4 Penilaian (skor 1–9)

Total skor tersimpan = **100** (20 supplier × 5 kriteria), rentang skor **1–9**.

Contoh — **Supplier 15** (peringkat 1):

| Kriteria | Bobot | Skor | Bobot × Skor |
|---|---|---|---|
| C1 | 0,532962 | 7 | 3,730734 |
| C2 | 0,259994 | 8 | 2,079952 |
| C3 | 0,122922 | 9 | 1,106298 |
| C4 | 0,056393 | 1 | 0,056393 |
| C5 | 0,027730 | 2 | 0,055460 |
| **Total** | | | **7,028837** |

### 7.5 Hasil Ranking (Top 5)

| Peringkat | Supplier | Nilai Akhir |
|---|---|---|
| 1 | Supplier 15 | 7,028837 |
| 2 | Supplier 5 | 7,028837 |
| 3 | Supplier 16 | 6,922540 |
| 4 | Supplier 6 | 6,922540 |
| 5 | Supplier 14 | 6,536373 |

> **Rekomendasi sistem: Supplier 15.**

---

## 8. Akun Demo

| Username | Password | Level |
|---|---|---|
| `ambatukam` | `solides123098` | Admin — kelola seluruh data |
| `rusdi` | `solides123098` | Pimpinan — dashboard & cetak |

---

## 9. Instalasi & Menjalankan

**Kebutuhan:** PHP 7+ (ekstensi `mysqli`), MySQL 5.7+/8, atau XAMPP/WAMP/Laragon.

```bash
# 1. Impor struktur + data awal
mysql -u root -p < database/init.sql

# 2. Jalankan server (root proyek)
php -S localhost:8000

# 3. Buka
#    http://localhost:8000
```

Konfigurasi koneksi DB di `config/database.php` (default: `root` / kosong / `spk_supplier`).

### Konfigurasi Produksi (VPS)

- **Kredensial DB**: atur lewat env `DB_HOST`, `DB_USER`, `DB_PASS`, `DB_NAME`, atau salin `config/database.local.example.php` → `config/database.local.php` lalu isi kredensialnya (file ini tidak ikut di-git). Gunakan user MySQL non-root dengan password kuat (`bind-address=127.0.0.1`).
- **Sembunyikan error**: set env `APP_ENV=prod` (melalui php-fpm pool `env[APP_ENV]` + `clear_env=no`, atau dimanapun Anda menjalankan PHP). Saat `prod`, `display_errors` dimatikan dan pesan error DB diganti pesan generik; detail masuk ke error log.
- **Security headers** (`X-Frame-Options`, `X-Content-Type-Options`, `Referrer-Policy`) dikirim otomatis oleh `config/config.php`; pastikan HTTPS aktif supaya cookie sesi (`SOLIDES_SESSID`) ikut `Secure`.
- **Blokir di web server**: akses ke `/config/`, `/functions/`, `/layouts/`, `/database/`, `/.git`, dan file `*.sql`/`*.md`.

---

## 10. Struktur Folder

```
.
├── auth/            # Login, logout, proses login
├── config/          # BASE_URL, database, session
├── functions/       # init, auth, AHP, ranking (orchestrasi & repository)
├── layouts/         # header, sidebar, footer, layout
├── pages/
│   ├── ahp/         # Penilaian supplier (form + proses)
│   ├── dashboard.php
│   ├── hasil/       # Cetak laporan
│   ├── kriteria/    # CRUD + hitung bobot AHP
│   ├── proyek/      # CRUD + relasi supplier
│   ├── supplier/    # CRUD
│   └── user/        # CRUD akun (admin)
├── assets/          # bootstrap, bootstrap-icons, css, js, logo
└── database/init.sql
```