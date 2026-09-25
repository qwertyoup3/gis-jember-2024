<?php
// Sesuaikan dengan pengaturan MySQL Anda (default XAMPP: root tanpa password)
$host = 'localhost';
$user = 'root';
$pass = '';
$db   = 'db_jember';

$conn = new mysqli($host, $user, $pass, $db);
if ($conn->connect_error) {
    die('Koneksi database gagal: ' . $conn->connect_error);
}
$conn->set_charset('utf8mb4');
