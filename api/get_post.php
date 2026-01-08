<?php
// api/get_post.php
header("Content-Type: application/json; charset=UTF-8");
require_once 'db_connect.php';

$id = isset($_GET['id']) ? (int) $_GET['id'] : 0;

if ($id <= 0) {
    http_response_code(400);
    echo json_encode(["error" => true, "message" => "Invalid ID"]);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT * FROM board_webzine WHERE seq = :id");
    $stmt->execute(['id' => $id]);
    $post = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($post) {
        echo json_encode($post);
    } else {
        http_response_code(404);
        echo json_encode(["error" => true, "message" => "Post not found"]);
    }
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["error" => true, "message" => "Database error"]);
}
?>