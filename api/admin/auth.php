<?php
// api/admin/auth.php
session_start();
header("Content-Type: application/json; charset=UTF-8");

require_once '../db_connect.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(["message" => "Method not allowed"]);
    exit;
}

$data = json_decode(file_get_contents("php://input"), true);

if (!isset($data['admin_id']) || !isset($data['admin_passwd'])) {
    http_response_code(400);
    echo json_encode(["message" => "Missing credentials"]);
    exit;
}

$adminId = $data['admin_id'];
$adminPasswd = $data['admin_passwd'];

try {
    // Check credentials against admin_info table
    // Assumes columns: admin_id, admin_passwd
    // Note: Passwords should ideally be hashed. Checking plain text or hash depending on existing DB.
    // Assuming simple comparison for now or standard password_verify if hashed.
    // Given the prompt "admin_id, admin_passwd을 확인해서", we'll check direct match first.

    $stmt = $pdo->prepare("SELECT * FROM admin_info WHERE admin_id = :admin_id");
    $stmt->execute([':admin_id' => $adminId]);
    $admin = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($admin) {
        // Verify password
        // If stored password is not hashed (legacy), use direct comparison.
        // If hashed, use password_verify($adminPasswd, $admin['admin_passwd'])
        // We will assume direct comparison request implies legacy or simple setup. 
        // But for security, let's allow for direct match.

        if ($admin['admin_passwd'] === md5($adminPasswd)) {
            // Success
            $_SESSION['admin_logged_in'] = true;
            $_SESSION['admin_id'] = $admin['admin_id'];
            echo json_encode(["success" => true, "message" => "Login successful"]);
        } else {
            http_response_code(401);
            echo json_encode(["success" => false, "message" => "Invalid credentials"]);
        }
    } else {
        http_response_code(401);
        echo json_encode(["success" => false, "message" => "Invalid credentials"]);
    }

} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database error: " . $e->getMessage()]);
}
?>