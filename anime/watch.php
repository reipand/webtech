<?php
require_once 'config/db.php';
require_once 'config/security.php';

if (!isset($_GET['id'])) {
    header('Location: index.php');
    exit;
}

$anime_id = $_GET['id'];
$database = new Database();
$db = $database->getConnection();

// Get anime details
$query = "SELECT * FROM anime WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$anime_id]);
$anime = $stmt->fetch(PDO::FETCH_ASSOC);

if (!$anime) {
    header('Location: index.php');
    exit;
}

// Increment view count
$query = "UPDATE anime SET views = views + 1 WHERE id = ?";
$stmt = $db->prepare($query);
$stmt->execute([$anime_id]);

// Get related anime
<<<<<<< HEAD
$query = "SELECT id, title, cover_image FROM anime WHERE genre LIKE ? AND id != ? LIMIT 4";
=======
$query = "SELECT id, title, poster_image FROM anime WHERE genre LIKE ? AND id != ? LIMIT 4";
>>>>>>> 64ffc3ce046639559a138471d5f24f95d2b7d7a4
$stmt = $db->prepare($query);
$stmt->execute(["%" . $anime['genre'] . "%", $anime_id]);
$related_animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($anime['title']) ?> - Anime Universe</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="assets/css/style.css" rel="stylesheet">
    <link href="https://cdn.plyr.io/3.7.2/plyr.css" rel="stylesheet">
</head>
<body>
    <!-- Navbar -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-dark">
        <div class="container">
            <a class="navbar-brand" href="#">Anime Universe</a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="navbarNav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link active" href="#">Home</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Anime List</a></li>
                    <li class="nav-item"><a class="nav-link" href="#">Genres</a></li>
                </ul>
                <form class="d-flex">
                    <input class="form-control me-2" type="search" placeholder="Cari anime...">
                    <button class="btn btn-outline-light" type="submit">Search</button>
                </form>
                <ul class="navbar-nav ms-3">
                    <li class="nav-item"><a class="nav-link" href="login.php">Login</a></li>
                </ul>
            </div>
        </div>
    </nav>

    <div class="container my-5">
        <div class="row">
            <div class="col-lg-8">
                <h1><?= htmlspecialchars($anime['title']) ?></h1>
                <div class="anime-meta mb-4">
                    <span class="badge bg-primary me-2"><?= htmlspecialchars($anime['genre']) ?></span>
                    <span class="badge bg-secondary me-2"><?= htmlspecialchars($anime['release_year']) ?></span>
                    <span class="badge bg-info"><?= htmlspecialchars($anime['studio']) ?></span>
                </div>
                <!-- Video Player -->
                <div class="video-player mb-4">
                    <video id="player" playsinline controls>
                        <source src="<?= htmlspecialchars($anime['video_url']) ?>" type="video/mp4">
                    </video>
                </div>
                <!-- Anime Description -->
                <div class="anime-description mb-4">
                    <h3>Synopsis</h3>
                    <p><?= nl2br(htmlspecialchars($anime['synopsis'])) ?></p>
                </div>
            </div>
            <!-- Sidebar -->
            <div class="col-lg-4">
                <!-- Watchlist Button -->
                <div class="card mb-4">
                    <div class="card-body">
                        <?php if (isset($_SESSION['user_id'])) : ?>
                            <button class="btn btn-primary w-100" id="addToWatchlist">
                                Add to Watchlist
                            </button>
                        <?php else : ?>
                            <a href="login.php" class="btn btn-primary w-100">
                                Login to Add to Watchlist
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
                <!-- Related Anime -->
                <div class="card">
                    <div class="card-header">
                        <h4>Related Anime</h4>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <?php foreach ($related_animes as $related): ?>
                                <div class="col-6 mb-3">
                                    <a href="watch.php?id=<?= $related['id'] ?>">
                                        <img src="<?= htmlspecialchars($related['cover_image']) ?>" 
                                             alt="<?= htmlspecialchars($related['title']) ?>" 
                                             class="img-fluid rounded">
                                        <h6 class="mt-2"><?= htmlspecialchars($related['title']) ?></h6>
                                    </a>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.plyr.io/3.7.2/plyr.js"></script>
    <script>
        // Initialize video player
        const player = new Plyr('#player');

        // Add to watchlist functionality
        document.getElementById('addToWatchlist').addEventListener('click', function() {
            // Check if user is logged in
            fetch('api/auth/check_login.php')
                .then(response => response.json())
                .then(data => {
                    if (data.loggedIn) {
                        // Add to watchlist
                        fetch('api/watchlist.php', {
                            method: 'POST',
                            headers: {
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                anime_id: <?= $anime_id ?>,
                                action: 'add'
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            alert(data.message);
                        });
                    } else {
                        alert('Please login to add to watchlist');
                        window.location.href = 'login.php';
                    }
                });
        });
    </script>
</body>
</html>