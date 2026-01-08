<?php
header("Access-Control-Allow-Origin: *");
header("Content-Type: application/json; charset=UTF-8");
header("Access-Control-Allow-Methods: POST");

// Check method
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

// Check if file is uploaded
if (!isset($_FILES['image']) || $_FILES['image']['error'] !== UPLOAD_ERR_OK) {
    http_response_code(400);
    echo json_encode(["message" => "No file uploaded or upload error"]);
    exit;
}

// UUID Generation
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

try {
    $file = $_FILES['image'];
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

    // Validate extension
    $allowed = ['jpg', 'jpeg', 'png', 'gif', 'webp', 'mp4', 'webm', 'ogg'];
    if (!in_array($ext, $allowed)) {
        throw new Exception("Invalid file type");
    }

    // Directory: ../upload/temp/Y/m/d/
    // Start from api folder -> go up -> upload -> temp
    $baseDir = '../upload/temp/';
    $datePath = date('Y') . '/' . date('m') . '/' . date('d') . '/';
    $targetDir = $baseDir . $datePath;

    if (!file_exists($targetDir)) {
        if (!mkdir($targetDir, 0777, true)) {
            throw new Exception("Failed to create directory");
        }
    }

    $uuid = gen_uuid();
    $filename = $uuid . '.' . $ext;
    $targetPath = $targetDir . $filename;

    if (move_uploaded_file($file['tmp_name'], $targetPath)) {
        // Return relative path suitable for web (img src)
        // Adjust this depending on where your index.html is. 
        // If index.html is at root, and api is at /api, then ../upload/temp is /upload/temp
        // We will return a path relative to the domain root ideally, or relative to the page.
        // Let's assume root-relative path: /upload/temp/Y/m/d/filename
        $webPath = '/upload/temp/' . $datePath . $filename;

        echo json_encode([
            "success" => true,
            "url" => $webPath,
            "filename" => $filename
        ]);
    } else {
        throw new Exception("Failed to save file");
    }

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(["message" => $e->getMessage()]);
}
?>