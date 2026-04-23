<?php
/**
 * File: dashboard.php
 * Fungsi Utama: Menangani fungsionalitas rendering HTML Dashboard,
 * termasuk proteksi otentikasi bahwa hanya user terdaftar (designer) yang diizinkan.
 */

// Menentukan letak directory session untuk menghindari tumpang-tindih session dalam OS server lokal.
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');

// Mulai/gabung dengan session pengunjung yang ada di Request.
session_start();

// Validasi Keamanan: Jika pengunjung tidak memiliki indeks 'user_id' pada sesi 
// (berarti belum menjalankan prosedur di login.php), usir kembali secara paksa (Redirect)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Skema Otorisasi atau Hak Preferensi: Pastikan hanya pengguna berperan 'designer' 
// yang boleh menampilkan tampilan dashboard.php sepenuhnya. Jika bukan, alihkan (redirect).
if (isset($_SESSION['privilege']) && $_SESSION['privilege'] !== 'designer') {
    // Alihkan peran lain (mungkin general user/viewer) ke layar information object viewer
    header("Location: information.php");
    exit;
}

// Seandainya seluruh cek validasi di atas berbunyi aman, render struktur HTML view
// Memasukkan template UI dashboard.html ke buffer keluaran tanpa mengurai logika terpisah agar bersih.
include 'dashboard.html';
?>
