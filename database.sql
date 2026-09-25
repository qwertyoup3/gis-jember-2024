-- Database: Penduduk Kabupaten Jember 2024 (Sumber: BPS Kabupaten Jember, Tabel 3.1.1)
CREATE DATABASE IF NOT EXISTS db_jember CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE db_jember;

DROP TABLE IF EXISTS kecamatan;
CREATE TABLE kecamatan (
    id               INT AUTO_INCREMENT PRIMARY KEY,
    nama             VARCHAR(50)   NOT NULL,
    jumlah_penduduk  INT           NOT NULL,           -- jiwa, tahun 2024
    laju_pertumbuhan DECIMAL(5,2)  NOT NULL,           -- % per tahun, 2020-2024
    latitude         DECIMAL(10,6) NOT NULL,           -- titik di dalam wilayah kecamatan (dari batas GeoJSON)
    longitude        DECIMAL(10,6) NOT NULL
) ENGINE=InnoDB;

INSERT INTO kecamatan (nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude) VALUES
('Kencong',      71155, -0.42, -8.285510, 113.358112),
('Gumuk Mas',    90255,  0.01, -8.333965, 113.413900),
('Puger',       126660,  0.19, -8.328785, 113.477199),
('Wuluhan',     129414,  0.56, -8.351245, 113.542359),
('Ambulu',      121482,  0.64, -8.379440, 113.612737),
('Tempurejo',    82126,  0.38, -8.419075, 113.752272),
('Silo',        112043,  0.43, -8.258970, 113.867990),
('Mayang',       52840,  0.56, -8.206520, 113.811729),
('Mumbulsari',   70473,  0.53, -8.255755, 113.742095),
('Jenggawah',    91828,  0.58, -8.288960, 113.631917),
('Ajung',        86033,  0.62, -8.239380, 113.660642),
('Rambipuji',    88684,  0.15, -8.230265, 113.598181),
('Balung',       84749,  0.42, -8.273950, 113.519597),
('Umbulsari',    79411, -0.27, -8.243335, 113.416520),
('Semboro',      50011, -0.21, -8.179055, 113.432464),
('Jombang',      56241, -0.35, -8.224315, 113.355439),
('Sumberbaru',  116359, -0.33, -8.091140, 113.410436),
('Tanggul',      94169, -0.24, -8.100540, 113.501462),
('Bangsalsari', 128748,  0.46, -8.117990, 113.565351),
('Panti',        67654,  0.52, -8.076495, 113.618488),
('Sukorambi',    42929,  0.69, -8.129035, 113.662086),
('Arjasa',       43286,  0.77, -8.101945, 113.734209),
('Pakusari',     47131,  0.74, -8.154100, 113.774940),
('Kalisat',      80671,  0.28, -8.124300, 113.807580),
('Ledokombo',    70559,  0.36, -8.137980, 113.941442),
('Sumberjambe',  65112,  0.67, -8.069785, 113.932088),
('Sukowono',     62498,  0.59, -8.058165, 113.823701),
('Jelbuk',       33938,  0.86, -8.046210, 113.707806),
('Kaliwates',   127701,  0.60, -8.173680, 113.688720),
('Sumbersari',  137792,  0.92, -8.173635, 113.728461),
('Patrang',     103922,  0.51, -8.126835, 113.700827);
