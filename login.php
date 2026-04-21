<?php
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Ambil data user dari tabel user_table. Gunakan Prepared Statements demi keamanan (mencegah SQL Injection).
    $stmt = $conn->prepare("SELECT user_id, username, password, privilege FROM user_table WHERE username = ?");
    $stmt->bind_param("s", $username);
    $stmt->execute();
    $result = $stmt->get_result();

    if ($row = $result->fetch_assoc()) {
        // Cek password secara plain-text. (Jika di database password sudah di hash, gunakan password_verify($password, $row['password']))
        if ($password === $row['password']) {
            // Berhasil login, simpan session
            $_SESSION['user_id'] = $row['user_id'];
            $_SESSION['username'] = $row['username'];
            $_SESSION['privilege'] = $row['privilege'];
            
            echo json_encode(['status' => 'success']);
            exit;
        }
    }
    
    // Gagal login
    echo json_encode(['status' => 'error', 'message' => 'Username atau Password salah.']);
    exit;
}
?>