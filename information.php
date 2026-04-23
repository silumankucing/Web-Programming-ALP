<?php
/**
 * File: information.php
 * Kegunaan: Bertindak sebatas "pelindung/pengatur sesi" bagi template statis 'information.html'
 */
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');

// Lanjutkan (atau mulai jika belum ada) state data interaksi web menggunakan sistem cookie terenkripsi PHP
session_start();

// Periksa apakah kredensial otentifikasi user ada, apabila null (null reference di PHP), berarti user ilegal / belum masuk
if (!isset($_SESSION['user_id'])) {
    // Alihkan pembaca (Client User-Agent Browser) kembali ke portal depan secara HTTP (Tanpa dirender).
    header("Location: login.php");
    
    // Terminasi komputasi demi cegah peretasan header (Keluaran HTML bisa lanjut kalau eksekusi PHP tidak dihimbau mati).
    exit;
}

// Meleburkan berkas "view template" Information kedalam halaman skrip yang aman (authorized access)
include 'information.html';

// Penutup Skrip PHP tidak diperlukan jika berisi PHP murni namun ini membantu membaca logika file
?>
