<?php
/**
 * File: db_connect.php
 * Deskripsi: Skrip sentral untuk mengatur penyambungan (connection) ke Database MariaDB/MySQL. 
 * Seluruh file PHP yang membutuhkan akses ke database akan meng-include atau require file ini.
 */

// Ganti parameter koneksi ini sesuai dengan kredensial database lokal server Nginx (MySQL/MariaDB)
$host = 'localhost'; // Alamat server database
$db   = 'web_pro_alp'; // Nama skema database
$user = 'root'; // Username database 
$pass = '1182'; // Password database

// Membuat instance koneksi dengan pola Object-Oriented (OO) bawaan ekstensi mysqli
$conn = new mysqli($host, $user, $pass, $db);

// Menangkap kesalahan (Error) jika koneksi database gagal terbentuk. 
if ($conn->connect_error) {
    // Matikan eksekusi script dan tampilkan peringatan (hanya disarankan untuk environment pengembangan)
    die("Connection failed (Koneksi Gagal): " . $conn->connect_error);
}
?>
