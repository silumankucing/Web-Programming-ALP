<?php
/**
 * File: information_get_project_data.php
 * Deskripsi: API Endpoint yang akan mengambil informasi data 1 proyek beserta parts-nya (rakitan) dari db.
 * Ini dipanggil oleh information.js ketika viewer 3D diaktifkan.
 */
require 'db_connect.php';
header('Content-Type: application/json');

// Mencegah pengambilan data jika parameter project_id absen dalam URL GET parameter
if (!isset($_GET['project_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Project ID not provided']);
    exit;
}

$project_id = $_GET['project_id'];

// Ambil info nama file yang terelasi di tabel project_table menggunakan Prepared Statement
$stmt_proj = $conn->prepare("SELECT project_id, project_name, project_file FROM project_table WHERE project_id = ?");
$project_data = null;

if ($stmt_proj) {
    // "s" singkatan string. Ikat parameter dengan variabel 
    $stmt_proj->bind_param("s", $project_id);
    $stmt_proj->execute();
    $res_proj = $stmt_proj->get_result();
    
    if ($row_proj = $res_proj->fetch_assoc()) {
        $project_data = $row_proj; // Pindahkan ke project_data jika file tersedia
    }
    $stmt_proj->close(); // Tutup parameter prepared statement project.
}

// Kemudian kita ambil segala informasi tentang rakitan 'parts' yang terkait dengan project diatas.
$stmt = $conn->prepare("SELECT project_id, part_number, part_name, created_by, checked_by, approved_by, revision, manufacturing_process, material, progress, note FROM data_table WHERE project_id = ?");
if ($stmt) {
    $stmt->bind_param("s", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    // Looping memasukkan array associative row ke list array 
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    // Menghasilkan output sukses JSON berisikan project general & keseluruhan spesifikasi part-nya.
    echo json_encode(['status' => 'success', 'project' => $project_data, 'data' => $data]);
    $stmt->close();
} else {
    // Apabila MySQL gagal membentuk Query, laporkan.
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed']);
}
?>
