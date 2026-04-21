<?php
// Ganti parameter koneksi ini sesuai dengan database lokal Nginx (MySQL/MariaDB)
$host = 'localhost';
$db   = 'web_pro_alp';
$user = 'root'; 
$pass = '1182'; 

$conn = new mysqli($host, $user, $pass, $db);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
?>