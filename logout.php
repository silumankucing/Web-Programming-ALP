<?php
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();
// Hapus semua data session
session_destroy();
// Kembali ke halaman login
header("Location: login.html");
exit;
?>