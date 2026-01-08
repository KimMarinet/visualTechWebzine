<?php
// Set headers
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

require_once 'db_connect.php';

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

// Get POST data
$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['title']) || !isset($data['content']) || !isset($data['author'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing required fields"]);
    exit;
}

$title = $data['title'];
$author = $data['author'];
$summary = $data['summary'] ?? ''; // Default to empty string
$category = $data['category'] ?? 'General';
$content = $data['content'];
$date = date('Y.m.d'); // Asia/Seoul usually default in PHP config or set explicitly
date_default_timezone_set('Asia/Seoul');
$date = date('Y.m.d H:i:s');

// UUID Generation Function
function gen_uuid()
{
    return sprintf(
        '%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0x0fff) | 0x4000,
        mt_rand(0, 0x3fff) | 0x8000,
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff),
        mt_rand(0, 0xffff)
    );
}

// Function to process images in content
// Moves images from temp dir to final resources/uploads/posts dir
function processImages($htmlContent, $postSeq, $pdo)
{
    $dom = new DOMDocument();
    libxml_use_internal_errors(true);
    $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');

    // Directory settings
    // Temp dir root relative to this script: ../upload/temp/
    // Final dir: ../resources/uploads/posts/{Y}/{m}/{d}/
    $datePath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
    $finalBaseDir = '../resources/uploads/posts/';
    $finalTargetDir = $finalBaseDir . $datePath;

    if (!file_exists($finalTargetDir)) {
        mkdir($finalTargetDir, 0777, true);
    }

    foreach ($images as $img) {
        $src = $img->getAttribute('src');

        // Check if src points to temp upload
        // Our JS adds ../../upload/temp/... for display in write.php
        // Or /upload/temp/... depending on how it was saved.
        // We look for 'upload/temp' substring.
        if (strpos($src, 'upload/temp') !== false) {

            // Extract the relative path part starting from upload/temp...
            // e.g. ../../upload/temp/2026/01/08/uuid.jpg
            // or /upload/temp/2026/01/08/uuid.jpg

            $tempMarker = 'upload/temp/';
            $pos = strpos($src, $tempMarker);
            if ($pos !== false) {
                $relPath = substr($src, $pos); // upload/temp/2026/01/08/uuid.jpg

                // Construct physical source path
                // api/create_post.php is in api/
                // temp files are in ../upload/temp/...
                // So physical path is ../ + relPath
                $sourcePath = '../' . $relPath;

                if (file_exists($sourcePath)) {
                    $filename = basename($sourcePath); // uuid.jpg

                    // Move to final destination
                    // Final physical path: ../resources/uploads/posts/Y/m/d/uuid.jpg
                    $destPath = $finalTargetDir . $filename;

                    if (rename($sourcePath, $destPath)) {
                        // Success moving

                        // Update src to point to new location
                        // Web path: /resources/uploads/posts/Y/m/d/uuid.jpg
                        // Or relative: ../resources/uploads/posts/...
                        // Let's use a path similar format to temp: /resources/uploads/posts/...
                        // But wait, view.php needs to access it. 
                        // If view.php is in api/templates/, it needs ../../resources/uploads/posts/...
                        // Let's stick to the convention used in write.js which prepended ../.. to /upload/temp
                        // However, standard is usually root relative.
                        // Let's assume server root is configured.

                        $webFinalPath = '/resources/uploads/posts/' . $datePath . $filename;

                        $img->setAttribute('src', $webFinalPath);

                        // Insert into board_webzine_imageInfo
                        try {
                            $imgSql = "INSERT INTO board_webzine_imageInfo (post_seq, img_url) VALUES (:post_seq, :img_url)";
                            $imgStmt = $pdo->prepare($imgSql);
                            $imgStmt->execute([
                                ':post_seq' => $postSeq,
                                ':img_url' => $webFinalPath
                            ]);
                        } catch (PDOException $e) {
                            // Ignored
                        }
                    }
                }
            }
        }
    }

    return $dom->saveHTML();
}


// Database Insertion & Logic Flow
try {
    $pdo->beginTransaction();

    // 1. Insert Post FIRST (with placeholder or raw content initially)
    $sql = "INSERT INTO board_webzine (title, author, category, summary, content, date) VALUES (:title, :author, :category, :summary, :content, :date)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':category' => $category,
        ':summary' => $summary,
        ':content' => $content, // Raw content with temp URLs
        ':date' => $date
    ]);

    $postSeq = $pdo->lastInsertId();

    // 2. Process Images
    // This will move files, insert into imageInfo, and return clean HTML with new paths
    $processedContent = processImages($content, $postSeq, $pdo);

    // Remove doctype/html/body tags added by saveHTML
    $processedContent = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $processedContent));

    // Decode HTML entities back to UTF-8
    $processedContent = html_entity_decode($processedContent, ENT_QUOTES, 'UTF-8');
    $processedContent = trim($processedContent);

    // 3. Update Post with processed content
    $updateSql = "UPDATE board_webzine SET content = :content WHERE seq = :seq";
    $updateStmt = $pdo->prepare($updateSql);
    $updateStmt->execute([
        ':content' => $processedContent,
        ':seq' => $postSeq
    ]);

    $pdo->commit();

    echo json_encode(["message" => "Post created successfully", "seq" => $postSeq]);

} catch (Exception $e) {
    if ($pdo->inTransaction()) {
        $pdo->rollBack();
    }
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>