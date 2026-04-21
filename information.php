<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
// Render the HTML view
include 'information.html';
?><?php
session_start();
// Mencegah akses ke information 3D viewer jika sesi belum ada (belum login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>3D Model Viewer with Part Info</title>
    <link rel="stylesheet" href="information.css">
    <!-- Menggunakan Import Map untuk import Three.js tanpa bundler -->
    <script type="importmap">
      {
        "imports": {
          "three": "https://unpkg.com/three@0.160.0/build/three.module.js",
          "three/addons/": "https://unpkg.com/three@0.160.0/examples/jsm/"
        }
      }
    </script>
</head>
<body>
    <div id="canvas-container"></div>

    <!-- Panel Informasi yang muncul saat part diklik -->
    <div id="info-panel">
        <h3 id="panel-title">Project Information</h3>
        <table>
            <tbody>
                <tr>
                    <th>Part Number</th>
                    <td id="val-part_number">-</td>
                </tr>
                <tr>
                    <th>Part Name</th>
                    <td id="val-part_name">-</td>
                </tr>
                <tr>
                    <th>Created By</th>
                    <td id="val-created_by">-</td>
                </tr>
                <tr>
                    <th>Checked By</th>
                    <td id="val-checked_by">-</td>
                </tr>
                <tr>
                    <th>Approved By</th>
                    <td id="val-approved_by">-</td>
                </tr>
                <tr>
                    <th>Priviledge</th>
                    <td id="val-priviledge">-</td>
                </tr>
                <tr>
                    <th>Version</th>
                    <td id="val-version">-</td>
                </tr>
                <tr>
                    <th>Material</th>
                    <td id="val-material">-</td>
                </tr>
                <tr>
                    <th>Manufacturing Process</th>
                    <td id="val-manufacturing_process">-</td>
                </tr>
            </tbody>
        </table>
        <button id="close-btn">Close</button>
    </div>

    <!-- Notifikasi instruksi -->
    <div id="instruction">Click on any part of the 3D model to view its data.<br><small>Make sure to place your model at 'assets/model.glb'</small></div>

    <script type="module" src="information.js"></script>
</body>
</html>