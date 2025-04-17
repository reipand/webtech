<?php
require '../config/db.php';

if (isset($_GET['search'])) {
    $search = '%' . $_GET['search'] . '%';
    $stmt = $pdo->prepare("SELECT * FROM anime WHERE title LIKE ? OR genre LIKE ?");
    $stmt->execute([$search, $search]);
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);
}
?>