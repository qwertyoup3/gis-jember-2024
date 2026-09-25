<?php
// Mengembalikan data kecamatan dalam format JSON untuk dipakai Leaflet & Chart.js
header('Content-Type: application/json; charset=utf-8');
require 'koneksi.php';

$sql = "SELECT nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude
        FROM kecamatan ORDER BY jumlah_penduduk DESC";
$result = $conn->query($sql);

$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = [
        'nama'   => $row['nama'],
        'jumlah' => (int) $row['jumlah_penduduk'],
        'laju'   => (float) $row['laju_pertumbuhan'],
        'lat'    => (float) $row['latitude'],
        'lng'    => (float) $row['longitude'],
    ];
}

echo json_encode($data);
$conn->close();
