USE spk_supplier;

ALTER TABLE alternatif
ADD CONSTRAINT unik_alternatif_proyek_supplier UNIQUE (id_proyek, id_supplier),
ADD CONSTRAINT fk_alternatif_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_alternatif_supplier FOREIGN KEY (id_supplier) REFERENCES supplier(id_supplier) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE penilaian_supplier
ADD CONSTRAINT unik_penilaian_proyek_alt_kriteria UNIQUE (id_proyek, id_alternatif, id_kriteria),
ADD CONSTRAINT fk_penilaian_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_penilaian_alternatif FOREIGN KEY (id_alternatif) REFERENCES alternatif(id_alternatif) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_penilaian_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE perbandingan_ahp
ADD CONSTRAINT unik_perbandingan_ahp UNIQUE (id_proyek, id_kriteria_1, id_kriteria_2),
ADD CONSTRAINT fk_perbandingan_ahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_perbandingan_ahp_kriteria_1 FOREIGN KEY (id_kriteria_1) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_perbandingan_ahp_kriteria_2 FOREIGN KEY (id_kriteria_2) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE bobot_ahp
ADD CONSTRAINT unik_bobot_ahp UNIQUE (id_proyek, id_kriteria),
ADD CONSTRAINT fk_bobot_ahp_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_bobot_ahp_kriteria FOREIGN KEY (id_kriteria) REFERENCES kriteria(id_kriteria) ON DELETE CASCADE ON UPDATE CASCADE;

ALTER TABLE hasil_perhitungan
ADD CONSTRAINT unik_hasil_perhitungan UNIQUE (id_proyek, id_alternatif, metode),
ADD CONSTRAINT fk_hasil_proyek FOREIGN KEY (id_proyek) REFERENCES proyek(id_proyek) ON DELETE CASCADE ON UPDATE CASCADE,
ADD CONSTRAINT fk_hasil_alternatif FOREIGN KEY (id_alternatif) REFERENCES alternatif(id_alternatif) ON DELETE CASCADE ON UPDATE CASCADE;
