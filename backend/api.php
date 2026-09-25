<?php
/**
 * Dikerjakan oleh: Anggota 1 (Database & Backend)
 *
 * API data kecamatan — CRUD + Search + Sort (pakai mysqli, konsisten dengan koneksi.php)
 * Format field JSON disamakan dengan contoh dosen: nama, jumlah, laju, lat, lng
 * Dipakai oleh: peta Leaflet & Chart.js di index.php
 *
 * Endpoint:
 *   GET    api.php                      -> semua data (urut nama, atau pakai search/sort)
 *   GET    api.php?id=5                 -> satu kecamatan by id
 *   GET    api.php?search=jember        -> cari berdasarkan nama (LIKE)
 *   GET    api.php?sort_by=jumlah&order=desc  -> urutkan (sort_by: nama/jumlah/laju)
 *   POST   api.php                      -> tambah kecamatan baru (body JSON)
 *   PUT    api.php?id=5                 -> update kecamatan (body JSON)
 *   DELETE api.php?id=5                 -> hapus kecamatan
 */

header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');
require 'koneksi.php';

$method = $_SERVER['REQUEST_METHOD'];

// Pemetaan nama field JSON (singkat) -> nama kolom database (lengkap)
$kolom = [
    'nama'   => 'nama',
    'jumlah' => 'jumlah_penduduk',
    'laju'   => 'laju_pertumbuhan',
    'lat'    => 'latitude',
    'lng'    => 'longitude',
];

function bentukData($row) {
    return [
        'id'     => (int) $row['id'],
        'nama'   => $row['nama'],
        'jumlah' => (int) $row['jumlah_penduduk'],
        'laju'   => (float) $row['laju_pertumbuhan'],
        'lat'    => (float) $row['latitude'],
        'lng'    => (float) $row['longitude'],
    ];
}

switch ($method) {

    case 'GET':
        // GET satu data by id
        if (isset($_GET['id'])) {
            $stmt = $conn->prepare("SELECT * FROM kecamatan WHERE id = ?");
            $stmt->bind_param("i", $_GET['id']);
            $stmt->execute();
            $row = $stmt->get_result()->fetch_assoc();

            if (!$row) {
                http_response_code(404);
                echo json_encode(["error" => "Kecamatan tidak ditemukan"]);
                exit;
            }
            echo json_encode(bentukData($row));
            exit;
        }

        // GET semua data, dengan opsi search & sort
        $sql = "SELECT * FROM kecamatan";
        $where = "";
        $orderBy = "nama ASC";

        // Fitur Find/Search: cari kecamatan berdasarkan nama
        if (!empty($_GET['search'])) {
            $where = " WHERE nama LIKE ?";
        }

        // Fitur Sort: urutkan berdasarkan kolom tertentu
        if (!empty($_GET['sort_by']) && isset($kolom[$_GET['sort_by']])) {
            $order = (isset($_GET['order']) && strtolower($_GET['order']) === 'desc') ? 'DESC' : 'ASC';
            $orderBy = $kolom[$_GET['sort_by']] . " " . $order;
        }

        $sql .= $where . " ORDER BY " . $orderBy;

        if ($where !== "") {
            $stmt = $conn->prepare($sql);
            $like = "%" . $_GET['search'] . "%";
            $stmt->bind_param("s", $like);
            $stmt->execute();
            $result = $stmt->get_result();
        } else {
            $result = $conn->query($sql);
        }

        $data = [];
        while ($row = $result->fetch_assoc()) {
            $data[] = bentukData($row);
        }
        echo json_encode($data);
        break;

    case 'POST':
        // Fitur Create: tambah kecamatan baru
        $body = json_decode(file_get_contents("php://input"), true);

        if (!$body || empty($body['nama']) || !isset($body['jumlah'], $body['laju'], $body['lat'], $body['lng'])) {
            http_response_code(400);
            echo json_encode(["error" => "Data tidak lengkap. Wajib isi: nama, jumlah, laju, lat, lng"]);
            exit;
        }

        $stmt = $conn->prepare(
            "INSERT INTO kecamatan (nama, jumlah_penduduk, laju_pertumbuhan, latitude, longitude)
             VALUES (?, ?, ?, ?, ?)"
        );
        $stmt->bind_param(
            "siddd",
            $body['nama'],
            $body['jumlah'],
            $body['laju'],
            $body['lat'],
            $body['lng']
        );
        $stmt->execute();

        http_response_code(201);
        echo json_encode(["message" => "Kecamatan berhasil ditambahkan", "id" => $conn->insert_id]);
        break;

    case 'PUT':
        // Fitur Update (partial update, field yang dikirim aja yang diubah)
        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "id wajib diisi lewat query string, contoh: api.php?id=5"]);
            exit;
        }

        $body = json_decode(file_get_contents("php://input"), true);
        if (!$body) {
            http_response_code(400);
            echo json_encode(["error" => "Body request tidak valid"]);
            exit;
        }

        $fields = [];
        $types = "";
        $params = [];
        foreach ($kolom as $jsonField => $dbField) {
            if (isset($body[$jsonField])) {
                $fields[] = "$dbField = ?";
                $types .= ($jsonField === 'nama') ? "s" : "d";
                $params[] = $body[$jsonField];
            }
        }

        if (empty($fields)) {
            http_response_code(400);
            echo json_encode(["error" => "Tidak ada field yang diupdate"]);
            exit;
        }

        $types .= "i";
        $params[] = $_GET['id'];

        $stmt = $conn->prepare("UPDATE kecamatan SET " . implode(", ", $fields) . " WHERE id = ?");
        $stmt->bind_param($types, ...$params);
        $stmt->execute();

        echo json_encode(["message" => "Kecamatan berhasil diupdate"]);
        break;

    case 'DELETE':
        // Fitur Delete
        if (empty($_GET['id'])) {
            http_response_code(400);
            echo json_encode(["error" => "id wajib diisi lewat query string, contoh: api.php?id=5"]);
            exit;
        }

        $stmt = $conn->prepare("DELETE FROM kecamatan WHERE id = ?");
        $stmt->bind_param("i", $_GET['id']);
        $stmt->execute();

        echo json_encode(["message" => "Kecamatan berhasil dihapus"]);
        break;

    default:
        http_response_code(405);
        echo json_encode(["error" => "Method tidak didukung"]);
}

$conn->close();
