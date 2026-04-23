<?php
/**
 * File: login.php
 * Fungsi Utama: Menangani fungsionalitas rendering HTML login jika diakses normal (GET),
 * atau melakukan validasi login dengan database saat form disubmit via metode POST.
 */

// Menentukan letak directory session untuk menghindari tumpang-tindih (conflict session) OS
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');

// Menginisialisasi session di PHP
session_start();

// Periksa secara eksplisit jika pemintaan datangnya dari method HTTP (POST) dari AJAX Form
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Sisipkan konektor yang kita siapkan di file db_connect agar $conn tersedia.
    require 'db_connect.php';
    
    // Jadikan bentuk respon balasan (response) sebagai tipe JSON
    header('Content-Type: application/json');

    // Menerima kiriman body form (dari js / post). Jika nilai kosong, fallback menjadi string kosong.
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Ambil data user dari tabel user_table. Gunakan 'Prepared Statements' demi 
    // keamanan maksimal mencegah tindakan injeksi perintah SQL (SQL Injection).
    $stmt = $conn->prepare("SELECT user_id, username, password, privilege FROM user_table WHERE username = ?");
    
    // Bind string ('s') tipe variable username untuk menggantikan ? di statemen diatas
    $stmt->bind_param("s", $username);
    $stmt->execute();
    
    // Tarik output eksekusi select dari MySQL
    $result = $stmt->get_result();

    // Pastikan apakah row ditemukan (hasil tarikan ada di array assosiatif)
    if ($row = $result->fetch_assoc()) {
        // Cek input pass dengan data pass plain-text (Catatan: harusnya hashing sprt password_verify)
        if ($password === $row['password']) {
            // Berhasil mencocokan kredensial, mari kita ciptakan variabel sesi tersimpan.
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['privilege'] = $row['privilege'];
            
            // Berikan umpan balik JSON positif ke antarmuka aplikasi.
            echo json_encode(['status' => 'success']);
            exit; // Stop pengerjaan script lebih lanjut (Karena kita tak mau print layout HTML)
        }
    }
    
    // Jika logika flow gagal, respon gagal dikembalikan (Cegah bocornya detil error, tampilkan generik)
    echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah. (Kredensial tidak cocok!)']);
    exit;
}

// Jika permintaan selain POST (biasanya GET / render UI awal), maka include script HTML statis ini
include 'login.html';
?>
