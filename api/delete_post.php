<?php
// api/delete_post.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once 'db_connect.php';

// Check Admin Authentication
if (!isset($_SESSION['admin_logged_in']) || $_SESSION['admin_logged_in'] !== true) {
    http_response_code(403);
    echo json_encode(["message" => "Unauthorized access"]);
    exit;
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['seq'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing post ID"]);
    exit;
}

$postSeq = $data['seq'];

try {
    // 1. Get associated images to delete files?
    // Optional: Delete physical image files linked to this post.
    // For now, we will just delete the DB record. 
    // Garbage collector cleans up temp, but permanent files remain if we don't delete them.
    // Let's first delete the post.

    $pdo->beginTransaction();

    // Delete from imageInfo table first (foreign key constraint might exist, or just cleanup)
    $stmt = $pdo->prepare("DELETE FROM board_webzine_imageInfo WHERE post_seq = :seq");
    $stmt->execute([':seq' => $postSeq]);

    // Delete Post
    $stmt = $pdo->prepare("DELETE FROM board_webzine WHERE seq = :seq");
    $stmt->execute([':seq' => $postSeq]);

    if ($stmt->rowCount() > 0) {
        $pdo->commit();
        echo json_encode(["success" => true, "message" => "Post deleted successfully"]);
    } else {
        $pdo->rollBack();
        http_response_code(404);
        echo json_encode(["success" => false, "message" => "Post not found"]);
    }

} catch (PDOException $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>