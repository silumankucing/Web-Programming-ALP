<?php
ini_set('session.save_path', __DIR__ . DIRECTORY_SEPARATOR . 'sessions');
session_start();

// Mencegah akses ke dashboard jika sesi belum ada (belum login)
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

// Batasi hanya "designer" yang bisa mengakses dashboard
if (isset($_SESSION['privilege']) && $_SESSION['privilege'] !== 'designer') {
    header("Location: information.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Project Dashboard</title>
    <link rel="stylesheet" href="dashboard.css">
</head>
<body>
    <header>
        <h1>WELCOME, You Are Designer</h1>
    </header>

    <main>
        <div class="section-title">
            <h2>Select a Project</h2>
            <p>Choose an assembly or project to view its 3D model and part information.</p>
        </div>

        <div class="project-grid" id="project-container">
            <!-- Projects will be generated here by Javascript -->
        </div>
    </main>

    <!-- Modal Form untuk Upload Project -->
    <div id="upload-modal" class="modal-overlay hidden">
        <div class="modal-content">
            <div class="modal-header">
                <h3>Add New Project</h3>
                <span class="close-modal" id="close-modal">&times;</span>
            </div>
            <div class="modal-body">
                <form id="upload-form">
                    <div class="form-group">
                        <label for="project-name">Project Name</label>
                        <input type="text" id="project-name" required placeholder="e.g. Engine Block V3">
                    </div>
                    <div class="form-group">
                        <label for="project-file">Upload 3D Model (.glb)</label>
                        <input type="file" id="project-file" accept=".glb" required>
                    </div>
                    <button type="submit" class="submit-btn" id="btn-upload">Upload & Create Project</button>
                </form>
            </div>
        </div>
    </div>

    <script src="dashboard.js"></script>
</body>
</html>