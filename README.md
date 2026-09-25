# Visualisasi Penduduk Kabupaten Jember 2024 (PHP + MySQL + Leaflet)

## Isi folder
- `database.sql` — membuat database `db_jember`, tabel `kecamatan`, dan 31 data kecamatan
- `koneksi.php` — pengaturan koneksi MySQL
- `api.php` — endpoint JSON yang dibaca peta dan grafik
- `jember_kecamatan.geojson` — batas wilayah 31 kecamatan Jember (poligon)
- `index.php` — halaman utama: kartu ringkasan, peta Leaflet, grafik Chart.js, tabel

## Fitur peta
- Peta choropleth: tiap wilayah kecamatan diwarnai sesuai data
- Tombol ganti tema: **Jumlah Penduduk** / **Laju Pertumbuhan**
- Kontrol layer (pojok kanan atas):
  - Peta dasar (pilih satu): OpenStreetMap, Citra Satelit
  - Layer yang bisa dinyalakan/dimatikan: Batas Kecamatan, Label Nama Kecamatan, Lingkaran Jumlah Penduduk
- Panel info saat kursor di atas wilayah, popup saat diklik, legenda, skala

## Cara menjalankan (XAMPP)
1. Salin folder `jember-penduduk` ke `C:\xampp\htdocs\`.
2. Jalankan Apache dan MySQL di XAMPP Control Panel.
3. Buka `http://localhost/phpmyadmin` → tab **Import** → pilih `database.sql` → **Go**.
   (Import ulang jika sebelumnya sudah pernah; koordinat kecamatan diperbarui.)
4. Jika user/password MySQL berbeda, ubah di `koneksi.php`.
5. Buka `http://localhost/jember-penduduk/`.

Butuh koneksi internet untuk memuat Leaflet, Chart.js, dan peta dasar.

## Sumber data
- Penduduk: BPS Kabupaten Jember, Tabel 3.1.1 (2024)
- Batas kecamatan: github.com/Alf-Anas/batas-administrasi-indonesia (update 13 Juni 2023),
  disederhanakan (toleransi ±30 m) agar ringan dimuat di web. Data penduduk dan poligon
  dicocokkan berdasarkan nama kecamatan (Gumukmas → "Gumuk Mas" disesuaikan dengan BPS).
