<?php
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();
require 'db_connect.php';

header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Unauthorized']);
    exit;
}

$query = "SELECT project_id, project_name, project_file FROM project_table ORDER BY project_id DESC";
$result = $conn->query($query);

$projects = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $project_file = $row['project_file'];
        if ($project_file && strpos($project_file, 'assets/') !== 0 && strpos($project_file, 'projects/') !== 0) {
            $project_file = 'assets/' . $project_file;
        }

        $projects[] = [
            'id' => $row['project_id'],
            'name' => $row['project_name'],
            'project_file' => $project_file
        ];
    }
}

echo json_encode(['status' => 'success', 'data' => $projects]);
$conn->close();
?>