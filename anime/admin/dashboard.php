<?php
session_start();
require_once '../config/db.php';
require_once '../config/security.php';

// Check if user is logged in and is an admin
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header('Location: ../../login.php');
    exit;
}

$database = new Database();
$db = $database->getConnection();

// Initialize variables
$error_message = '';
$total_anime = 0;
$total_users = 0;
$recent_anime = [];
$recent_users = [];

// Get statistics for dashboard
try {
    // Total anime count
    $query = "SELECT COUNT(*) as total_anime FROM anime";
    $stmt = $db->query($query);
    $total_anime = $stmt->fetch(PDO::FETCH_ASSOC)['total_anime'];

    // Total users count
    $query = "SELECT COUNT(*) as total_users FROM users";
    $stmt = $db->query($query);
    $total_users = $stmt->fetch(PDO::FETCH_ASSOC)['total_users'];

    // Recent anime additions
    $query = "SELECT id, title, cover_image as poster_image, created_at FROM anime ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->query($query);
    $recent_anime = $stmt->fetchAll(PDO::FETCH_ASSOC);

    // Recent user registrations
    $query = "SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5";
    $stmt = $db->query($query);
    $recent_users = $stmt->fetchAll(PDO::FETCH_ASSOC);

} catch (PDOException $e) {
    $error_message = "Database error: " . $e->getMessage();
}

// Generate CSRF token
$csrf_token = generateCSRFToken();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - Anime Universe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <nav class="col-md-2 d-none d-md-block sidebar">
                <div class="sidebar-sticky">
                    <ul class="nav flex-column">
                        <li class="nav-item text-center mb-4">
                            <h4 class="text-white">Anime Universe</h4>
                            <small class="text-muted">Admin Panel</small>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link active" href="dashboard.php">
                                <i class="bi bi-speedometer2"></i> Dashboard
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_anime.php">
                                <i class="bi bi-film"></i> Manage Anime
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="manage_users.php">
                                <i class="bi bi-people"></i> Manage Users
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="reports.php">
                                <i class="bi bi-graph-up"></i> Reports
                            </a>
                        </li>
                        <li class="nav-item">
                            <a class="nav-link" href="settings.php">
                                <i class="bi bi-gear"></i> Settings
                            </a>
                        </li>
                        <li class="nav-item mt-4">
                            <a class="nav-link" href="../logout.php">
                                <i class="bi bi-box-arrow-right"></i> Logout
                            </a>
                        </li>
                    </ul>
                </div>
            </nav>

            <!-- Main Content -->
            <main role="main" class="col-md-9 ml-sm-auto col-lg-10 px-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Dashboard</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group mr-2">
                            <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                        </div>
                    </div>
                </div>

                <?php if (!empty($error_message)): ?>
                    <div class="alert alert-danger"><?= safeOutput($error_message) ?></div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="row mb-4">
                    <div class="col-md-6">
                        <div class="stat-card bg-primary">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="bi bi-film"></i>
                                    <div class="count"><?= $total_anime ?></div>
                                    <div class="label">Total Anime</div>
                                </div>
                                <div class="align-self-center">
                                    <a href="manage_anime.php" class="text-white">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-md-6">
                        <div class="stat-card bg-success">
                            <div class="d-flex justify-content-between">
                                <div>
                                    <i class="bi bi-people"></i>
                                    <div class="count"><?= $total_users ?></div>
                                    <div class="label">Total Users</div>
                                </div>
                                <div class="align-self-center">
                                    <a href="manage_users.php" class="text-white">View All</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Recent Anime -->
                <div class="card mb-4">
                    <div class="card-header">
                        <h5 class="mb-0">Recently Added Anime</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($recent_anime as $anime): ?>
                                <div class="col-md-2 mb-3">
                                    <div class="card card-anime h-100">
                                        <img src="<?= !empty($anime['poster_image']) ? safeOutput($anime['poster_image']) : '../assets/images/anime.jpg' ?>" 
                                             class="card-img-top" 
                                             alt="<?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?>">
                                        <div class="card-body">
                                            <h6 class="card-title"><?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?></h6>
                                            <small class="text-muted">Added: <?= date('M d, Y', strtotime($anime['created_at'])) ?></small>
                                        </div>
                                        <div class="card-footer bg-white">
                                            <a href="edit_anime.php?id=<?= $anime['id'] ?>" class="btn btn-sm btn-outline-primary">Edit</a>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>

                <!-- Recent Users -->
                <div class="card">
                    <div class="card-header">
                        <h5 class="mb-0">Recent User Registrations</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table recent-table">
                                <thead>
                                    <tr>
                                        <th>ID</th>
                                        <th>Username</th>
                                        <th>Email</th>
                                        <th>Registered</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($recent_users as $user): ?>
                                        <tr>
                                            <td><?= $user['id'] ?></td>
                                            <td><?= !empty($user['username']) ? safeOutput($user['username']) : 'N/A' ?></td>
                                            <td><?= !empty($user['email']) ? safeOutput($user['email']) : 'N/A' ?></td>
                                            <td><?= date('M d, Y', strtotime($user['created_at'])) ?></td>
                                            <td>
                                                <a href="edit_user.php?id=<?= $user['id'] ?>" class="btn btn-sm btn-outline-secondary">Edit</a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/admin.js"></script>
</body>
</html>