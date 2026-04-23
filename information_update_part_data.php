<?php
/**
 * File: information_update_part_data.php
 * Deskripsi: Menangani pengeditan atau penambahan baru informasi dari *part 3D model* ke Database MariaDB/MySQL.
 * Melakukan proteksi otorisasi secara ketat; 
 *  - "Operator" hanya bisa edit progress dan Note. 
 *  - "Manager" hanya bisa edit note.
 *  - "Designer" memegang kuasa memperbarui general info lainnya.
 */
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
require 'db_connect.php'; // Mengkoneksikan skrip ini ke Database
session_start();

// Mengekstrak label akses role (privilege) pengguna dari server session state
$privilege = isset($_SESSION['privilege']) ? $_SESSION['privilege'] : '';
if ($privilege !== 'Designer' && $privilege !== 'Operator' && $privilege !== 'Manager') {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized / Akses Ilegal!']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);

if (!isset($data['project_id']) || !isset($data['part_name'])) {
    echo json_encode(['status' => 'error', 'message' => 'Missing identifiers']);
    exit;
}

// Cek apakah data part ini sudah ada di dalam tabel
$check_stmt = $conn->prepare("SELECT part_number FROM data_table WHERE project_id=? AND part_name=?");
$check_stmt->bind_param("ss", $data['project_id'], $data['part_name']);
$check_stmt->execute();
$res = $check_stmt->get_result();
$exists = $res->num_rows > 0;
$check_stmt->close();

if (!$exists) {
    // Jika belum pernah ada di database, tambahkan baris baru dengan privilege bebas sebagai inisiasi
    $noteTemp = isset($data['note']) ? $data['note'] : '';
    $stmtInsert = $conn->prepare("INSERT INTO data_table (project_id, part_number, part_name, created_by, checked_by, approved_by, revision, manufacturing_process, material, progress, note) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    $p_num = isset($data['part_number']) ? $data['part_number'] : ('PN-' . $data['project_id']);
    $c_by = isset($data['created_by']) ? $data['created_by'] : '';
    $chk_by = isset($data['checked_by']) ? $data['checked_by'] : '';
    $a_by = isset($data['approved_by']) ? $data['approved_by'] : '';
    $rev = isset($data['revision']) ? $data['revision'] : '';
    $m_proc = isset($data['manufacturing_process']) ? $data['manufacturing_process'] : '';
    $mat = isset($data['material']) ? $data['material'] : '';
    $prog = isset($data['progress']) && $data['progress'] !== '' ? (int)$data['progress'] : 0;
    
    $stmtInsert->bind_param("sssssssssis", $data['project_id'], $p_num, $data['part_name'], $c_by, $chk_by, $a_by, $rev, $m_proc, $mat, $prog, $noteTemp);
    
    if ($stmtInsert->execute()) {
        echo json_encode(['status' => 'success', 'message' => 'New part inserted successfully']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Insert failed: ' . $stmtInsert->error]);
    }
    $stmtInsert->close();
    exit;
}

// Jika sudah ada, Update sesuai privilege-nya:
if ($privilege === 'Designer') {
    $stmt = $conn->prepare("UPDATE data_table SET part_number=?, created_by=?, checked_by=?, approved_by=?, revision=?, manufacturing_process=?, material=?, progress=?, note=? WHERE project_id=? AND part_name=?");
    if ($stmt) {
        $prog = isset($data['progress']) && $data['progress'] !== '' ? (int)$data['progress'] : 0;
        $stmt->bind_param("sssssssisss", $data['part_number'], $data['created_by'], $data['checked_by'], $data['approved_by'], $data['revision'], $data['manufacturing_process'], $data['material'], $prog, $data['note'], $data['project_id'], $data['part_name']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Part updated']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    }
} else if ($privilege === 'Operator') {
    $stmt = $conn->prepare("UPDATE data_table SET progress=?, note=? WHERE project_id=? AND part_name=?");
    if ($stmt) {
        $note = isset($data['note']) ? $data['note'] : '';
        $prog = isset($data['progress']) && $data['progress'] !== '' ? (int)$data['progress'] : 0;
        $stmt->bind_param("isss", $prog, $note, $data['project_id'], $data['part_name']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Part updated by Operator']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    }
} else if ($privilege === 'Manager') {
    $stmt = $conn->prepare("UPDATE data_table SET note=? WHERE project_id=? AND part_name=?");
    if ($stmt) {
        $note = isset($data['note']) ? $data['note'] : '';
        $stmt->bind_param("sss", $note, $data['project_id'], $data['part_name']);

        if ($stmt->execute()) {
            echo json_encode(['status' => 'success', 'message' => 'Note updated by Manager']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Update failed: ' . $stmt->error]);
        }
        $stmt->close();
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Query error: ' . $conn->error]);
    }
}
?>
