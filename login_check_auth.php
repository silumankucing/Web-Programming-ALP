<?php 
/**
 * File: login_check_auth.php
 * Deskripsi: API Endpoint Backend yang dipanggil oleh antarmuka (JS) atau fetch  
 * untuk memeriksa apakah pengguna (user) saat ini masih aktif login (menyimpan session valid). 
 */

// Set path penyimpanan session di dalam aplikasi (menghindari tumpang-tindih session sistem OS umum)
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions'); 

// Memulai session yang ada
session_start(); 

// Menyatakan header response yang dikirimkan adalah berupa format JSON murni
header('Content-Type: application/json');

// Membentuk struktur data respons
// Nilai logged_in menjadi true jika _SESSION['user_id'] ada, artinya user sukses login sebelumnya. 
$response = ['logged_in' => isset($_SESSION['user_id'])];

// Apabila user terkonfirmasi sudah login,
if ($response['logged_in']) {
    // Siapkan dan oper detail ringan mengenai entitas login user (username dan hak aksesnya) 
    $response['username'] = $_SESSION['username'] ?? '';
    $response['privilege'] = $_SESSION['privilege'] ?? '';
}

// Konversi format Array PHP menjadi struktur string object Data JSON dan kirimkan ke client
echo json_encode($response); 
?>
