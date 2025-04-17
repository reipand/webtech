<?php
require_once 'config/db.php';
require_once 'config/security.php'; // Make sure to include security.php

$database = new Database();
$db = $database->getConnection();

// Ambil anime populer
$query = "SELECT id, title, cover_image as cover_image, genre FROM anime ORDER BY views DESC LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$popular_animes = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Ambil anime terbaru
$query = "SELECT id, title, cover_image as cover_image, release_date as release_year FROM anime ORDER BY release_date DESC LIMIT 8";
$stmt = $db->prepare($query);
$stmt->execute();
$new_animes = $stmt->fetchAll(PDO::FETCH_ASSOC);
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Anime Universe - Portal Anime Terbaik</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/css/styles.css">
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

    <!-- Hero Banner -->
    <div class="hero-banner">
        <div class="container">
            <h1>Welcome to Anime Universe</h1>
            <p>Portal terbaik untuk menonton anime favoritmu</p>
        </div>
    </div>

    <!-- Popular Anime Section -->
    <section class="container my-5">
        <h2 class="section-title">Popular Anime</h2>
        <div class="row">
            <?php foreach ($popular_animes as $anime): ?>
            <div class="col-md-3 mb-4">
                <div class="anime-card">
                    <img src="<?= !empty($anime['poster_image']) ? safeOutput($anime['poster_image']) : 'assets/images/default-poster.jpg' ?>" 
                         alt="<?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?>" 
                         class="img-fluid">
                    <div class="anime-info">
                        <h5><?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?></h5>
                        <p><?= !empty($anime['genre']) ? substr(safeOutput($anime['genre']), 0, 100) : 'No genre specified' ?></p>
                        <a href="watch.php?id=<?= $anime['id'] ?>" class="btn btn-primary">Watch Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <!-- New Releases Section -->
    <section class="container my-5">
        <h2 class="section-title">New Releases</h2>
        <div class="row">
            <?php foreach ($new_animes as $anime): ?>
            <div class="col-md-3 mb-4">
                <div class="anime-card">
                    <img src="<?= !empty($anime['poster_image']) ? safeOutput($anime['poster_image']) : 'assets/images/default-poster.jpg' ?>" 
                         alt="<?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?>" 
                         class="img-fluid">
                    <div class="anime-info">
                        <h5><?= !empty($anime['title']) ? safeOutput($anime['title']) : 'Untitled Anime' ?></h5>
                        <p>Year: <?= !empty($anime['release_year']) ? safeOutput(substr($anime['release_year'], 0, 4)) : 'N/A' ?></p>
                        <a href="watch.php?id=<?= $anime['id'] ?>" class="btn btn-primary">Watch Now</a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </section>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/js/main.js"></script>
    <script>
        // Fetch recommendations from Node.js API
        fetch('/api/recommendations.php')
            .then(response => response.json())
            .then(data => {
                console.log('Recommendations:', data);
                // Process and display recommendations
            });
    </script>
</body>
</html>