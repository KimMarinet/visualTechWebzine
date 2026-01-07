<?php
$host = 'localhost';
$port = 3306;
$db_name = 'VISUALTECH';
$username = 'MS';
$password = '1234';

$dsn = "mysql:host=$host;port=$port;dbname=$db_name;charset=utf8mb4";

try {
    $pdo = new PDO($dsn, $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->setAttribute(PDO::ATTR_DEFAULT_FETCH_MODE, PDO::FETCH_ASSOC);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(["message" => "Database connection failed: " . $e->getMessage()]);
    exit;
}
?>