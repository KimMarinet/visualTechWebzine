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

$postImage = "";

// Function to process images in content
function processImages($htmlContent)
{
    global $postImage;
    $dom = new DOMDocument();
    // Suppress warnings for invalid HTML
    libxml_use_internal_errors(true);
    // Force UTF-8 encoding
    $dom->loadHTML(mb_convert_encoding($htmlContent, 'HTML-ENTITIES', 'UTF-8'), LIBXML_HTML_NOIMPLIED | LIBXML_HTML_NODEFDTD);
    libxml_clear_errors();

    $images = $dom->getElementsByTagName('img');
    $uploadsDir = '../uploads/';
    // Create uploads dir if not exists (although we created it manually)
    if (!file_exists($uploadsDir)) {
        mkdir($uploadsDir, 0777, true);
    }

    // Use a queue to safely modify the DOM while iterating
    $imagesToRemove = [];

    foreach ($images as $index => $img) {
        $src = $img->getAttribute('src');

        // Check if image is base64
        if (preg_match('/^data:image\/(\w+);base64,/', $src, $type)) {
            $data = substr($src, strpos($src, ',') + 1);
            $type = strtolower($type[1]); // jpg, png, gif

            if (!in_array($type, ['jpg', 'jpeg', 'gif', 'png'])) {
                continue;
            }

            $data = base64_decode($data);
            if ($data === false) {
                continue;
            }

            // Generate unique name
            $filename = uniqid() . '_' . time() . '.' . $type;
            $filepath = $uploadsDir . $filename;

            // Save file
            file_put_contents($filepath, $data);

            $newSrc = '../uploads/' . $filename;

            // Set main image if not set
            if (empty($postImage)) {
                $postImage = $filename; // Store filename only
            }

            // Mark for removal from content
            $imagesToRemove[] = $img;

        } else {
            // Existing image URL
            if (empty($postImage)) {
                $postImage = $src;
            }
            // Should we remove existing images too? The user said "preview added images".
            // Assuming user uploaded ones (Base64). If they pasted a URL, it's ambiguous.
            // But usually "added in preview" implies the uploaded ones.
            // However, to be consistent with "no images in content", maybe remove this too?
            // Let's stick to strict interpretation: "images added to preview" usually means the file uploads.
            // But if I paste a URL in editor, it is also "added".
            // Let's remove ALL images to avoid duplication if that is the goal.
            // However, the user specifically mentioned "uploaded images" in context of "preview".
            // Let's remove the ones we processed (Base64) to be safe.
            // Update: User said "images added to preview" (plural).
            // Let's remove the processed ones for now.
            $imagesToRemove[] = $img;
        }
    }

    foreach ($imagesToRemove as $img) {
        $img->parentNode->removeChild($img);
    }

    // Save modified HTML
    return $dom->saveHTML();
}

$processedContent = processImages($content);

// Remove doctype/html/body tags added by saveHTML
$processedContent = preg_replace('/^<!DOCTYPE.+?>/', '', str_replace(array('<html>', '</html>', '<body>', '</body>'), array('', '', '', ''), $processedContent));

// Decode HTML entities back to UTF-8 characters for clean database storage
$processedContent = html_entity_decode($processedContent, ENT_QUOTES, 'UTF-8');

// Strip HTML tags to save only plain text
// Convert block elements to newlines to preserve readability
$processedContent = str_replace(array('<br>', '<br />', '</p>', '</div>', '<h1>', '<h2>', '<h3>', '<h4>', '<h5>', '</h6>'), "\n", $processedContent);
$processedContent = strip_tags($processedContent);
$processedContent = trim($processedContent);

// Database Insertion
try {
    $sql = "INSERT INTO posts (title, author, category, summary, content, image_url, date) VALUES (:title, :author, :category, :summary, :content, :image_url, :date)";
    $stmt = $pdo->prepare($sql);

    $stmt->execute([
        ':title' => $title,
        ':author' => $author,
        ':category' => $category,
        ':summary' => $summary,
        ':content' => $processedContent,
        ':image_url' => $postImage,
        ':date' => $date
    ]);

    echo json_encode(["message" => "Post created successfully", "id" => $pdo->lastInsertId()]);

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>