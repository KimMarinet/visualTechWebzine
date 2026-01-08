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

// Include database connection
require_once 'db_connect.php';

// Handle GET request
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    try {
        $page = isset($_GET['page']) ? (int) $_GET['page'] : 1;
        $limit = isset($_GET['limit']) ? (int) $_GET['limit'] : 6;
        $offset = ($page - 1) * $limit;

        // Get total count
        $countStmt = $pdo->prepare("SELECT COUNT(*) FROM board_webzine");
        $countStmt->execute();
        $total = $countStmt->fetchColumn();

        // Get paginated posts
        $stmt = $pdo->prepare("SELECT * FROM board_webzine ORDER BY date DESC, seq DESC LIMIT :limit OFFSET :offset");
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        $stmt->execute();
        $posts = $stmt->fetchAll(PDO::FETCH_ASSOC);

        // Process posts to add image_url if missing, extracting from content
        foreach ($posts as &$post) {
            if (empty($post['image_url']) && !empty($post['content'])) {
                // Extract first image src from content
                if (preg_match('/<img[^>]+src="([^">]+)"/', $post['content'], $match)) {
                    $post['image_url'] = $match[1];
                }
            }
        }
        unset($post); // Break reference

        echo json_encode([
            'data' => $posts,
            'meta' => [
                'total' => $total,
                'page' => $page,
                'limit' => $limit,
                'totalPages' => ceil($total / $limit)
            ]
        ]);
    } catch (PDOException $e) {
        http_response_code(500);
        echo json_encode(["message" => "Error fetching posts: " . $e->getMessage()]);
    }
    exit;
}

http_response_code(405);
echo json_encode(["message" => "Method not allowed"]);
?>