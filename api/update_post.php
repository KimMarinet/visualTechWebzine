<?php
// api/update_post.php
// Handles updating an existing post
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

if (!isset($data['seq']) || !isset($data['title']) || !isset($data['content']) || !isset($data['author'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}

$seq = $data['seq'];
$title = $data['title'];
$author = $data['author'];
$summary = $data['summary'] ?? '';
$category = $data['category'] ?? 'General';
$content = $data['content'];

// No UUID generation needed for update unless we process new images separately without hook?
// The write.js handles image uploads via hook to temp/ -> we just need to move them if they are new.

// Function to process images in content (Same as create_post but adapted if needed)
function processImages($htmlContent, $postSeq, $pdo)
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    // Use UTF-8 hack
    $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');

    $datePath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
    $finalBaseDir = '../resources/uploads/posts/';
    $finalTargetDir = $finalBaseDir . $datePath;

    if (!file_exists($finalTargetDir)) {
        mkdir($finalTargetDir, 0777, true);
    }

    foreach ($images as $img) {
        $src = $img->getAttribute('src');

        // Only process NEW temp images
        if (strpos($src, 'upload/temp') !== false) {
            $tempMarker = 'upload/temp/';
            $pos = strpos($src, $tempMarker);
            if ($pos !== false) {
                $relPath = substr($src, $pos);
                $sourcePath = '../' . $relPath;

                if (file_exists($sourcePath)) {
                    $filename = basename($sourcePath);
                    $destPath = $finalTargetDir . $filename;

                    if (rename($sourcePath, $destPath)) {
                        $webFinalPath = '/resources/uploads/posts/' . $datePath . $filename;
                        $img->setAttribute('src', $webFinalPath);

                        // Insert into board_webzine_imageInfo (associate with this post)
                        try {
                            $imgSql = "INSERT INTO board_webzine_imageInfo (post_seq, img_url) VALUES (:post_seq, :img_url)";
                            $imgStmt = $pdo->prepare($imgSql);
                            $imgStmt->execute([
                                ':post_seq' => $postSeq,
                                ':img_url' => $webFinalPath
                            ]);
                        } catch (PDOException $e) {
                        }
                    }
                }
            }
        }
    }
    return $dom->saveHTML();
}

try {
    $pdo->beginTransaction();

    // 1. Process Images first to get clean content
    // Note: We process images before updating content.
    $processedContent = processImages($content, $seq, $pdo);

    // Remove doctype/html/body tags
    $processedContent = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $processedContent));
    $processedContent = html_entity_decode($processedContent, ENT_QUOTES, 'UTF-8');
    $processedContent = trim($processedContent);

    // 2. Update Post
    $sql = "UPDATE board_webzine SET title = :title, author = :author, category = :category, summary = :summary, content = :content WHERE seq = :seq";
    $stmt = $pdo->prepare($sql);
    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':category' => $category,
        ':summary' => $summary,
        ':content' => $processedContent,
        ':seq' => $seq
    ]);

    $pdo->commit();

    echo json_encode(["message" => "Post updated successfully", "seq" => $seq]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>