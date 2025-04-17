<?php
session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

if (!isset($_GET['id'])) {
    header('Location: manage_anime.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

$error_message = '';
$success_message = '';

// Initialize anime details with default values
$anime = [
    'title' => '',
    'synopsis' => '',
    'genre' => '',
    'release_year' => '',
    'studio' => '',
    'video_url' => '',
    'poster_image' => ''
];

// Get anime details
try {
    $query = "SELECT * FROM anime WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$_GET['id']]);
    $anime = $stmt->fetch(PDO::FETCH_ASSOC) ?: $anime; // Use defaults if no data found
} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token";
    } else {
        $title = sanitizeInput($_POST['title'] ?? '');
        $synopsis = sanitizeInput($_POST['synopsis'] ?? '');
        $genre = sanitizeInput($_POST['genre'] ?? '');
        $release_year = (int)($_POST['release_year'] ?? 0);
        $studio = sanitizeInput($_POST['studio'] ?? '');
        $video_url = sanitizeInput($_POST['video_url'] ?? '');
        $poster_image = sanitizeInput($_POST['poster_image'] ?? '');

        // Validate required fields
        if (empty($title) || empty($genre) || empty($video_url) || empty($poster_image)) {
            $error_message = "Please fill in all required fields";
        } else {
            try {
                $query = "UPDATE anime SET 
                          title = ?, synopsis = ?, genre = ?, release_year = ?, 
                          studio = ?, video_url = ?, poster_image = ?
                          WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([
                    $title, $synopsis, $genre, $release_year, 
                    $studio, $video_url, $poster_image, $_GET['id']
                ]);
                
                $success_message = "Anime updated successfully!";
                // Refresh anime data
                $query = "SELECT * FROM anime WHERE id = ?";
                $stmt = $db->prepare($query);
                $stmt->execute([$_GET['id']]);
                $anime = $stmt->fetch(PDO::FETCH_ASSOC) ?: $anime;
            } catch (PDOException $e) {
                $error_message = "Database error: " . $e->getMessage();
            }
        }
    }
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Anime - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Edit Anime</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="manage_anime.php" class="btn btn-sm btn-outline-secondary">
                    <i class="bi bi-arrow-left"></i> Back to Anime List
                </a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($error_message) ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="alert alert-success"><?= htmlspecialchars($success_message) ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <form method="POST">
                    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>">
                    
                    <div class="mb-3">
                        <label for="title" class="form-label">Title *</label>
                        <input type="text" class="form-control" id="title" name="title" 
                               value="<?= htmlspecialchars($anime['title'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="synopsis" class="form-label">Synopsis</label>
                        <textarea class="form-control" id="synopsis" name="synopsis" rows="3"><?= htmlspecialchars($anime['synopsis'] ?? '') ?></textarea>
                    </div>
                    
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="genre" class="form-label">Genre *</label>
                            <input type="text" class="form-control" id="genre" name="genre" 
                                   value="<?= htmlspecialchars($anime['genre'] ?? '') ?>" required>
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="release_year" class="form-label">Release Year</label>
                            <input type="number" class="form-control" id="release_year" name="release_year" 
                                   value="<?= htmlspecialchars($anime['release_year'] ?? '') ?>">
                        </div>
                        <div class="col-md-3 mb-3">
                            <label for="studio" class="form-label">Studio</label>
                            <input type="text" class="form-control" id="studio" name="studio" 
                                   value="<?= htmlspecialchars($anime['studio'] ?? '') ?>">
                        </div>
                    </div>
                    
                    <div class="mb-3">
                        <label for="video_url" class="form-label">Video URL *</label>
                        <input type="url" class="form-control" id="video_url" name="video_url" 
                               value="<?= htmlspecialchars($anime['video_url'] ?? '') ?>" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="poster_image" class="form-label">Poster Image URL *</label>
                        <input type="url" class="form-control" id="poster_image" name="poster_image" 
                               value="<?= htmlspecialchars($anime['poster_image'] ?? '') ?>" required>
                        <div class="mt-2">
                            <img id="poster_preview" src="<?= htmlspecialchars($anime['poster_image'] ?? 'assets/images/placeholder.jpg') ?>" 
                                 style="max-height: 200px;">
                        </div>
                    </div>
                    
                    <button type="submit" class="btn btn-primary">Update Anime</button>
                </form>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Preview poster image when URL changes
        document.getElementById('poster_image').addEventListener('input', function() {
            document.getElementById('poster_preview').src = this.value || 'assets/images/placeholder.jpg';
        });
    </script>
</body>
</html>