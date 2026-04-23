<?php
/**
 * File: dashboard_create_project.php
 * Deskripsi: Skrip pemrosesan API untuk menerima unggahan (Upload) File model 3D (ekstensi .GLB).
 * Termasuk mengecek validitas tipe file, error PHP upload, mengamankan nama unik,  
 * dan menambahkannya sebagai baris baru ke tabel 'project_table' DB.
 */
// Deteksi AWAL jika file melebih batas upload `post_max_size` PHP 
// (karena saat ini terjadi, PHP bisa mengosongkan semua isi $_POST, $_FILES, dan kadang memutus $_SESSION)
if (isset($_SERVER['CONTENT_LENGTH']) && (int)$_SERVER['CONTENT_LENGTH'] > 0 && empty($_POST) && empty($_FILES)) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => 'Ukuran file terlalu besar! PHP memblokir request karena melebihi batas "post_max_size" atau "upload_max_filesize" di konfigurasi php.ini Anda.']);
    exit;
}

ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();
header('Content-Type: application/json');

// Mencegah akses yang belum Auth
if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $projectName = $_POST['projectName'] ?? '';

    // Pastikan Nama dan File di Input
    if (empty($projectName) || !isset($_FILES['projectFile'])) {
        echo json_encode(['status' => 'error', 'message' => 'Nama Project dan File 3D harus diisi. Pastikan ukuran file tidak melebihi batas.']);
        exit;
    }

    $file = $_FILES['projectFile'];

    // Validasi ekstensi harus .glb
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if ($ext !== 'glb') {
        echo json_encode(['status' => 'error', 'message' => 'Hanya file .glb yang diizinkan!']);
        exit;
    }

    // Cek error payload upload
    if ($file['error'] !== UPLOAD_ERR_OK) {
        echo json_encode(['status' => 'error', 'message' => 'Error selama proses file upload. Error code: ' . $file['error']]);
        exit;
    }

    // Membersihkan Nama Project agar tidak ada karakter terlarang
    $folderName = preg_replace('/[^A-Za-z0-9_\-\s]/', '_', $projectName);
    $targetDir = __DIR__ . DIRECTORY_SEPARATOR . 'assets';

    // Pastikan folder assets ada (biasanya sudah ada, tapi untuk jaga-jaga)
    if (!is_dir($targetDir)) {
        mkdir($targetDir, 0777, true);
    }

    // Nama file dan penempatan pada server
    $fileName = basename($file['name']);
    $targetFile = $targetDir . DIRECTORY_SEPARATOR . $fileName;

    // Mulai Memindahkan dari temporary direktori PHP ke direktori assets
    if (move_uploaded_file($file['tmp_name'], $targetFile)) {
        
        require 'db_connect.php';
        
        $insertQuery = "INSERT INTO project_table (project_name, project_file) VALUES (?, ?)";
        $stmt = $conn->prepare($insertQuery);
        
        if ($stmt) {
            // Memasukkan data ke DB: Nama Project dan File (serta ekstensinya, tanpa path tambahan)
            $stmt->bind_param("ss", $projectName, $fileName);
            $stmt->execute();
            $stmt->close();
        }
        $conn->close();
        
        echo json_encode([
            'status' => 'success', 
            'folder' => 'assets/' . $fileName
        ]);
        exit;
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Gagal memindahkan dan menyimpan file yang diupload.']);
        exit;
    }

} else {
    // Apabila request method bukan POST
    echo json_encode(['status' => 'error', 'message' => 'Metode request tidak diizinkan.']);
}
?>