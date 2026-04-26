# KONTRAK TEKNIS METODE AHP DAN F-AHP

# Sistem Pendukung Keputusan Pemilihan Supplier Proyek Pembangunan

## PT Solides Propertindo

---

## 1. Identitas Dokumen

| Informasi     | Keterangan                                                          |
| ------------- | ------------------------------------------------------------------- |
| Nama Dokumen  | metode-ahp-fahp.md                                                  |
| Jenis Dokumen | Kontrak Teknis Perhitungan Metode                                   |
| Aplikasi      | Sistem Pendukung Keputusan Pemilihan Supplier                       |
| Metode        | Analytical Hierarchy Process dan Fuzzy Analytical Hierarchy Process |
| Teknologi     | PHP Native, Bootstrap, MySQL                                        |
| Studi Kasus   | PT Solides Propertindo                                              |
| Status        | Draft Final Awal                                                    |
| Versi         | 1.0                                                                 |

---

## 2. Tujuan Dokumen

Dokumen ini dibuat sebagai pedoman utama dalam implementasi metode **Analytical Hierarchy Process (AHP)** dan **Fuzzy Analytical Hierarchy Process (F-AHP)** pada aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

Dokumen ini bertujuan untuk:

1. Menentukan alur perhitungan metode AHP.
2. Menentukan alur perhitungan metode F-AHP.
3. Menentukan struktur input yang dibutuhkan oleh setiap metode.
4. Menentukan rumus yang digunakan dalam sistem.
5. Menentukan aturan validasi perhitungan.
6. Menentukan output dari masing-masing metode.
7. Menentukan cara penyimpanan hasil perhitungan ke database.
8. Menentukan aturan ranking supplier.
9. Menentukan aturan perbandingan hasil AHP dan F-AHP.
10. Menjaga agar implementasi metode tetap konsisten sampai aplikasi selesai.

---

## 3. Ruang Lingkup Metode

Metode yang digunakan dalam aplikasi ini adalah:

1. **Analytical Hierarchy Process (AHP)**  
   Digunakan untuk menghitung bobot kriteria berdasarkan perbandingan berpasangan menggunakan nilai crisp/skala pasti.

2. **Fuzzy Analytical Hierarchy Process (F-AHP)**  
   Digunakan untuk menghitung bobot kriteria berdasarkan perbandingan berpasangan menggunakan nilai linguistik yang dikonversi ke bilangan fuzzy triangular.

3. **Ranking Supplier**  
   Menggunakan bobot hasil AHP dan F-AHP untuk menghitung nilai akhir supplier berdasarkan nilai supplier terhadap setiap kriteria.

4. **Perbandingan Metode**  
   Membandingkan hasil ranking supplier dari metode AHP dan F-AHP.

---

## 4. Batasan Metode

Agar implementasi tetap fokus dan mudah diselesaikan, metode dibatasi sebagai berikut:

1. Perhitungan dilakukan pada level **kriteria utama**.
2. Subkriteria tidak digunakan pada versi awal aplikasi.
3. Setiap proyek memiliki data perhitungan sendiri.
4. AHP digunakan untuk menghasilkan bobot kriteria crisp.
5. F-AHP digunakan untuk menghasilkan bobot kriteria fuzzy.
6. Ranking supplier dihitung berdasarkan bobot kriteria dan nilai supplier.
7. Nilai supplier dinormalisasi berdasarkan jenis kriteria, yaitu `benefit` atau `cost`.
8. Metode AHP wajib melalui uji konsistensi.
9. Metode F-AHP menggunakan pendekatan Triangular Fuzzy Number.
10. Hasil akhir ranking disimpan ke tabel `hasil_perhitungan`.

---

# BAGIAN A

# KONTRAK METODE ANALYTICAL HIERARCHY PROCESS

---

## 5. Tujuan Metode AHP

Metode AHP digunakan untuk menentukan bobot prioritas setiap kriteria dalam pemilihan supplier.

Bobot kriteria diperoleh dari hasil perbandingan berpasangan antar kriteria yang diinput oleh Admin.

Hasil utama metode AHP adalah:

1. Matriks perbandingan berpasangan.
2. Matriks normalisasi.
3. Bobot prioritas kriteria.
4. Nilai lambda maksimum.
5. Nilai Consistency Index.
6. Nilai Consistency Ratio.
7. Status konsistensi.
8. Ranking supplier berdasarkan bobot AHP.

---

## 6. Input Metode AHP

Input metode AHP berasal dari:

1. Data proyek.
2. Data kriteria.
3. Nilai perbandingan antar kriteria.
4. Nilai supplier terhadap setiap kriteria.

### 6.1 Data Proyek

Data proyek digunakan agar setiap proses perhitungan terikat pada proyek tertentu.

Tabel database:

```text
proyek
```
