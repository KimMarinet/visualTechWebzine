<?php
// Set headers for CORS and JSON content
header("Access-Control-Allow-Origin: *");
header("Access-Control-Allow-Methods: GET, OPTIONS");
header("Content-Type: application/json; charset=UTF-8");

// Handle preflight requests
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    http_response_code(200);
    exit;
}

// Define data file path
$logFile = __DIR__ . '/data/posts.json';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    if (file_exists($logFile)) {
        $jsonData = file_get_contents($logFile);
        echo $jsonData;
    } else {
        echo json_encode([]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);
?>