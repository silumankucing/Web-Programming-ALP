<?php 
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions'); 
session_start(); 
header('Content-Type: application/json');

$response = ['logged_in' => isset($_SESSION['user_id'])];

if ($response['logged_in']) {
    $response['username'] = $_SESSION['username'] ?? '';
    $response['privilege'] = $_SESSION['privilege'] ?? '';
}

echo json_encode($response); 
?>