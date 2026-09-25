<?php
// Sesuaikan dengan pengaturan MySQL (default XAMPP: root tanpa password)
$host = 'localhost';
$user = 'root';
$pass = 'root123';
$db   = 'jember-penduduk';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
