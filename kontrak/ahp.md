# KONTRAK TEKNIS METODE AHP

Dokumen ini menjadi pedoman implementasi metode Analytical Hierarchy Process pada aplikasi Sistem Pendukung Keputusan Pemilihan Supplier.

## Alur Perhitungan

1. Ambil data kriteria.
2. Ambil nilai perbandingan berpasangan.
3. Bentuk matriks AHP.
4. Hitung total kolom.
5. Normalisasi matriks.
6. Hitung bobot prioritas.
7. Hitung weighted sum vector.
8. Hitung consistency vector.
9. Hitung lambda maksimum, CI, dan CR.
10. Simpan bobot jika proses selesai.

## Aturan Konsistensi

Bobot dinyatakan konsisten jika nilai CR kurang dari atau sama dengan 0,10.
