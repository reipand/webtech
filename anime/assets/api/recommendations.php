<?php
session_start();
require_once '../config/db.php';

$database = new Database();
$db = $database->getConnection();

try {
    // Get top 10 recommended anime based on ratings
    $query = "SELECT * FROM anime ORDER BY rating DESC LIMIT 10";
    $stmt = $db->prepare($query);
    $stmt->execute();
    $results = $stmt->fetchAll(PDO::FETCH_ASSOC);

    echo json_encode($results);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['error' => 'Server Error', 'message' => $e->getMessage()]);
}
?>