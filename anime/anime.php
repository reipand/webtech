<?php
require '../config/db.php';

// Read Anime
function getAnimeList($pdo) {
    $stmt = $pdo->query("SELECT * FROM anime");
    return $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Create Anime
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['add_anime'])) {
    $title = $_POST['title'];
    $year = $_POST['year'];
    $genre = $_POST['genre'];
    $studio = $_POST['studio'];
    $description = $_POST['description'];

    $stmt = $pdo->prepare("INSERT INTO anime (title, year, genre, studio, description) VALUES (?, ?, ?, ?, ?)");
    $stmt->execute([$title, $year, $genre, $studio, $description]);
    header("Location: anime_list.php");
}

// Delete Anime
if (isset($_GET['delete_id'])) {
    $id = $_GET['delete_id'];
    $stmt = $pdo->prepare("DELETE FROM anime WHERE id = ?");
    $stmt->execute([$id]);
    header("Location: anime_list.php");
}
?>