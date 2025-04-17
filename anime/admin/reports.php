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

// Get report data
try {
    // Anime by genre
    $query = "SELECT genre, COUNT(*) as count FROM anime GROUP BY genre ORDER BY count DESC";
    $anime_by_genre = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Users by registration date (last 30 days)
    $query = "SELECT DATE(created_at) as date, COUNT(*) as count 
              FROM users 
              WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
              GROUP BY DATE(created_at) 
              ORDER BY date";
    $users_by_date = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // Top viewed anime
    $query = "SELECT title, views FROM anime ORDER BY views DESC LIMIT 10";
    $top_viewed = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

    // User role distribution
    $query = "SELECT role, COUNT(*) as count FROM users GROUP BY role";
    $role_distribution = $db->query($query)->fetchAll(PDO::FETCH_ASSOC);

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
    <title>Reports - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <link rel="stylesheet" href="../../assets/css/admin.css">
</head>
<body>
    <?php include 'sidebar.php'; ?>

    <main class="main-content">
        <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
            <h1 class="h2">Reports & Analytics</h1>
            <div class="btn-toolbar mb-2 mb-md-0">
                <div class="btn-group me-2">
                    <button type="button" class="btn btn-sm btn-outline-secondary">Export</button>
                </div>
            </div>
        </div>

        <?php if (isset($error_message)): ?>
            <div class="alert alert-danger"><?= safeOutput($error_message) ?></div>
        <?php endif; ?>

        <div class="row mb-4">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>Anime by Genre</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="genreChart" height="300"></canvas>
                    </div>
                </div>
            </div>
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h5>User Role Distribution</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="roleChart" height="300"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="row mb-4">
            <div class="col-md-12">
                <div class="card">
                    <div class="card-header">
                        <h5>User Registrations (Last 30 Days)</h5>
                    </div>
                    <div class="card-body">
                        <canvas id="userRegChart" height="150"></canvas>
                    </div>
                </div>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h5>Top Viewed Anime</h5>
            </div>
            <div class="card-body">
                <div class="table-responsive">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Rank</th>
                                <th>Anime Title</th>
                                <th>Views</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($top_viewed as $index => $anime): ?>
                            <tr>
                                <td><?= $index + 1 ?></td>
                                <td><?= safeOutput($anime['title']) ?></td>
                                <td><?= number_format($anime['views']) ?></td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </main>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Anime by Genre Chart
        const genreCtx = document.getElementById('genreChart').getContext('2d');
        const genreChart = new Chart(genreCtx, {
            type: 'bar',
            data: {
                labels: <?= json_encode(array_column($anime_by_genre, 'genre')) ?>,
                datasets: [{
                    label: 'Number of Anime',
                    data: <?= json_encode(array_column($anime_by_genre, 'count')) ?>,
                    backgroundColor: 'rgba(54, 162, 235, 0.7)',
                    borderColor: 'rgba(54, 162, 235, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });

        // User Role Distribution Chart
        const roleCtx = document.getElementById('roleChart').getContext('2d');
        const roleChart = new Chart(roleCtx, {
            type: 'pie',
            data: {
                labels: <?= json_encode(array_column($role_distribution, 'role')) ?>,
                datasets: [{
                    data: <?= json_encode(array_column($role_distribution, 'count')) ?>,
                    backgroundColor: [
                        'rgba(255, 99, 132, 0.7)',
                        'rgba(54, 162, 235, 0.7)'
                    ],
                    borderColor: [
                        'rgba(255, 99, 132, 1)',
                        'rgba(54, 162, 235, 1)'
                    ],
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true
            }
        });

        // User Registrations Chart
        const userRegCtx = document.getElementById('userRegChart').getContext('2d');
        const userRegChart = new Chart(userRegCtx, {
            type: 'line',
            data: {
                labels: <?= json_encode(array_column($users_by_date, 'date')) ?>,
                datasets: [{
                    label: 'New Users',
                    data: <?= json_encode(array_column($users_by_date, 'count')) ?>,
                    backgroundColor: 'rgba(75, 192, 192, 0.2)',
                    borderColor: 'rgba(75, 192, 192, 1)',
                    borderWidth: 2,
                    tension: 0.1,
                    fill: true
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        ticks: {
                            stepSize: 1
                        }
                    }
                }
            }
        });
    </script>
</body>
</html>