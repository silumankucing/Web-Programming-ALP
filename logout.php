<?php
/**
 * File: logout.php
 * Deskripsi: File ini bertanggung jawab untuk menangani proses logout pengguna.
 * Fungsi utama adalah menghentikan sesi aktif dan mengarahkan pengguna kembali ke halaman utama (login).
 */

// Menentukan direktori penyimpanan sesi (sessions) yang aman
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');

// Memulai atau melanjutkan sesi yang sudah ada
session_start();

// Hapus semua data yang tersimpan di memori sesi saat ini
session_destroy();

// Redirect atau kembalikan pengguna ke halaman antarmuka login (login.html)
header("Location: login.php");
// Pastikan skrip berhenti dieksekusi setelah pengarahan header
exit;
?>
