<?php
require 'db_connect.php';
header('Content-Type: application/json');

if (!isset($_GET['project_id'])) {
    echo json_encode(['status' => 'error', 'message' => 'Project ID not provided']);
    exit;
}

$project_id = $_GET['project_id'];

// Get project info
$stmt_proj = $conn->prepare("SELECT project_id, project_name, project_file FROM project_table WHERE project_id = ?");
$project_data = null;
if ($stmt_proj) {
    $stmt_proj->bind_param("s", $project_id);
    $stmt_proj->execute();
    $res_proj = $stmt_proj->get_result();
    if ($row_proj = $res_proj->fetch_assoc()) {
        $project_data = $row_proj;
    }
    $stmt_proj->close();
}

$stmt = $conn->prepare("SELECT project_id, part_number, part_name, created_by, checked_by, approved_by, revision, manufacturing_process, material, progress, note FROM data_table WHERE project_id = ?");
if ($stmt) {
    $stmt->bind_param("s", $project_id);
    $stmt->execute();
    $result = $stmt->get_result();

    $data = [];
    while ($row = $result->fetch_assoc()) {
        $data[] = $row;
    }

    echo json_encode(['status' => 'success', 'project' => $project_data, 'data' => $data]);
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Query preparation failed']);
}
?>