<?php
require 'db_connect.php';
$result = $conn->query("SELECT * FROM data_table LIMIT 5");
$data = [];
while ($row = $result->fetch_assoc()) {
    $data[] = $row;
}
echo json_encode($data, JSON_PRETTY_PRINT);
?>
