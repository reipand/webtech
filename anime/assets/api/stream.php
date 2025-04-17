<?php
header('Content-Type: application/json');
require_once '../config/db.php';

// Get anime ID from query parameter
if (!isset($_GET['animeId'])) {
    http_response_code(400); // Bad Request
    echo json_encode(['error' => 'Missing animeId parameter']);
    exit;
}

$animeId = (int)$_GET['animeId']; // Sanitize input

try {
    $database = new Database();
    $db = $database->getConnection();

    // Query to fetch video URL for the given anime ID
    $query = "SELECT video_url FROM anime WHERE id = ?";
    $stmt = $db->prepare($query);
    $stmt->execute([$animeId]);
    $result = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$result) {
        http_response_code(404); // Not Found
        echo json_encode(['error' => 'Anime not found']);
        exit;
    }

    // Return JSON response
    echo json_encode(['video_url' => $result['video_url']]);
} catch (PDOException $e) {
    // Handle database errors
    http_response_code(500);
    echo json_encode(['error' => 'Server Error', 'message' => $e->getMessage()]);
}
?>