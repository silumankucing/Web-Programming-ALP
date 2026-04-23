<?php
/**
 * File: dashboard_list_projects.php
 * Deskripsi: Endpoint API (Backend) yang digunakan untuk menyajikan daftar/kumpulan proyek
 * dari database project_table. Hanya digunakan pada halaman Dashboard oleh role manajer/desainer.
 */

// Menjaga sesi tidak bocor dan menggunakan folder internal proyek (sessions).
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();

// Panggil setup MySQLi dari db_connect.php
require 'db_connect.php';

// Pastikan keluaran berbentuk Response JSON
header('Content-Type: application/json');

// Mencegah eksploitasi jika user awam mencoba memanggil langsung endpoint tanpa login
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

// Menyiapkan kueri database (mengurutkan berdasarkan ID project secara menurun/terbaru di atas)
$query = "SELECT project_id, project_name, project_file FROM project_table ORDER BY project_id DESC";
$result = $conn->query($query);

// Deklarasi array penampung
$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $project_file = $row['project_file'];
        
        // Jika string path tidak langsung berada di ssets/, tambahkan path 'assets/' di depan
        if ($project_file && strpos($project_file, 'assets/') !== 0 && strpos($project_file, 'projects/') !== 0) {
            $project_file = 'assets/' . $project_file;
        }

        // Susun objek JSON yang siap untuk di-render oleh Frontend
        $projects[] = [
            'id' => $row['project_id'],
            'name' => $row['project_name'],
            'project_file' => $project_file
        ];
    }
}

// Tutup koneksi agar resource dihemat
echo json_encode(['status' => 'success', 'data' => $projects]);
$conn->close();
?>
