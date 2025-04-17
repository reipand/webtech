<?php
session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Check admin access
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Handle anime deletion
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['delete_anime'])) {
    if (!verifyCSRFToken($_POST['csrf_token'])) {
        $error_message = "Invalid CSRF token";
    } else {
        $anime_id = $_POST['anime_id'];
        try {
            $query = "DELETE FROM anime WHERE id = ?";
            $stmt = $db->prepare($query);
            $stmt->execute([$anime_id]);
            $success_message = "Anime deleted successfully";
        } catch (PDOException $e) {
            $error_message = "Database error: " . $e->getMessage();
        }
    }
}

// Get all anime with pagination
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 10;
$offset = ($page - 1) * $per_page;

try {
    // Get total count
    $count_query = "SELECT COUNT(*) FROM anime";
    $total_anime = $db->query($count_query)->fetchColumn();
    $total_pages = ceil($total_anime / $per_page);

    // Get anime for current page
    $query = "SELECT id, title, cover_image, genre, release_date, views, created_at 
              FROM anime ORDER BY created_at DESC LIMIT ? OFFSET ?";
    $stmt = $db->prepare($query);
    $stmt->bindValue(1, $per_page, PDO::PARAM_INT);
    $stmt->bindValue(2, $offset, PDO::PARAM_INT);
    $stmt->execute();
    $animes = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Anime - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Manage Anime</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <a href="add_anime.php" class="btn btn-sm btn-outline-success">
                    <i class="bi bi-plus-circle"></i> Add New Anime
                </a>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= safeOutput($error_message) ?></div>
        <?php elseif (isset($success_message)): ?>
            <div class="alert alert-success"><?= safeOutput($success_message) ?></div>
        <?php endif; ?>

        <div class="table-responsive">
            <table class="table table-striped table-hover">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Poster</th>
                        <th>Title</th>
                        <th>Genre</th>
                        <th>Year</th>
                        <th>Views</th>
                        <th>Added</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($animes as $anime): ?>
                    <tr>
                        <td><?= $anime['id'] ?></td>
                        <td>
                            <img src="<?= safeOutput($anime['cover_image']) ?>" alt="Poster" style="height:50px;">
                        </td>
                        <td><?= safeOutput($anime['title']) ?></td>
                        <td><?= safeOutput($anime['genre']) ?></td>
                        <td><?= $anime['release_date'] ?></td>
                        <td><?= number_format($anime['views']) ?></td>
                        <td><?= date('M d, Y', strtotime($anime['created_at'])) ?></td>
                        <td>
                            <a href="edit_anime.php?id=<?= $anime['id'] ?>" class="btn btn-sm btn-outline-primary">
                                <i class="bi bi-pencil"></i>
                            </a>
                            <form method="POST" style="display:inline;">
                                <input type="hidden" name="csrf_token" value="<?= $csrf_token ?>">
                                <input type="hidden" name="anime_id" value="<?= $anime['id'] ?>">
                                <button type="submit" name="delete_anime" class="btn btn-sm btn-outline-danger" 
                                        onclick="return confirm('Are you sure you want to delete this anime?')">
                                    <i class="bi bi-trash"></i>
                                </button>
                            </form>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <nav aria-label="Anime pagination">
            <ul class="pagination justify-content-center">
                <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>" aria-label="Previous">
                            <span aria-hidden="true">&laquo;</span>
                        </a>
                    </li>
                <?php endif; ?>

                <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?page=<?= $i ?>"><?= $i ?></a>
                    </li>
                <?php endfor; ?>

                <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>" aria-label="Next">
                            <span aria-hidden="true">&raquo;</span>
                        </a>
                    </li>
                <?php endif; ?>
            </ul>
        </nav>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>