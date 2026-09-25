<?php
/**
 * SPK (Sistem Pendukung Keputusan) — Metode SAW (Simple Additive Weighting)
 * Dikerjakan oleh: Anggota ... (SPK)
 *
 * Tujuan: mengurutkan/merangking kecamatan berdasarkan prioritas pembangunan,
 * dengan kriteria & bobot yang bisa diatur sendiri oleh pengguna (dinamis).
 *
 * Kriteria default:
 *   C1 = jumlah_penduduk   (benefit) -> makin besar penduduk, makin butuh prioritas layanan publik
 *   C2 = laju_pertumbuhan  (benefit) -> makin cepat tumbuh, makin butuh perhatian pembangunan
 *
 * Endpoint:
 *   GET spk.php?w_jumlah=0.6&w_laju=0.4
 *     -> hitung normalisasi SAW, kalikan bobot, jumlahkan, urutkan desc, kembalikan JSON
 *
 * Catatan: jika w_jumlah + w_laju tidak dikirim, default 0.6 dan 0.4 (total = 1).
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require 'koneksi.php';

// Ambil bobot dari query string, default 0.6 & 0.4
$wJumlah = isset($_GET['w_jumlah']) ? (float) $_GET['w_jumlah'] : 0.6;
$wLaju   = isset($_GET['w_laju'])   ? (float) $_GET['w_laju']   : 0.4;

// Normalisasi bobot supaya totalnya selalu 1 (biar hasil tetap valid walau user salah isi)
$totalBobot = $wJumlah + $wLaju;
if ($totalBobot <= 0) {
    http_response_code(400);
    echo json_encode(["error" => "Bobot tidak valid"]);
    exit;
}
$wJumlah = $wJumlah / $totalBobot;
$wLaju   = $wLaju / $totalBobot;

$result = $conn->query("SELECT id, nama, jumlah_penduduk, laju_pertumbuhan FROM kecamatan");
$rows = [];
while ($r = $result->fetch_assoc()) {
    $rows[] = $r;
}

if (empty($rows)) {
    echo json_encode([]);
    exit;
}

// Cari nilai maksimum tiap kriteria (kedua kriteria = benefit, jadi normalisasi = nilai / max)
// Untuk laju_pertumbuhan yang bisa negatif, geser dulu supaya nilai minimum jadi 0
$maxJumlah = max(array_column($rows, 'jumlah_penduduk'));
$minLaju   = min(array_column($rows, 'laju_pertumbuhan'));
$lajuGeser = array_map(fn($r) => $r['laju_pertumbuhan'] - $minLaju, $rows); // semua jadi >= 0
$maxLajuGeser = max($lajuGeser) ?: 1; // hindari bagi 0

$hasil = [];
foreach ($rows as $i => $r) {
    $normJumlah = $maxJumlah > 0 ? $r['jumlah_penduduk'] / $maxJumlah : 0;
    $normLaju   = $lajuGeser[$i] / $maxLajuGeser;

    $skor = ($wJumlah * $normJumlah) + ($wLaju * $normLaju);

    $hasil[] = [
        'id'              => (int) $r['id'],
        'nama'            => $r['nama'],
        'jumlah_penduduk' => (int) $r['jumlah_penduduk'],
        'laju_pertumbuhan'=> (float) $r['laju_pertumbuhan'],
        'norm_jumlah'     => round($normJumlah, 4),
        'norm_laju'       => round($normLaju, 4),
        'skor'            => round($skor, 4),
    ];
}

// Urutkan berdasarkan skor tertinggi -> prioritas utama
usort($hasil, fn($a, $b) => $b['skor'] <=> $a['skor']);

// Tambahkan ranking
foreach ($hasil as $i => &$h) {
    $h['ranking'] = $i + 1;
}

echo json_encode([
    'bobot' => ['jumlah_penduduk' => round($wJumlah, 3), 'laju_pertumbuhan' => round($wLaju, 3)],
    'data'  => $hasil
]);

$conn->close();
