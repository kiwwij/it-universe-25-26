<?php
mb_internal_encoding("UTF-8"); 
header("Content-Type: application/json; charset=utf-8");
session_start();

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Not authorized']);
    exit();
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['message'])) {
    require_once "db_connect.php";
    $conn->set_charset("utf8mb4");

    $admin_id = $_SESSION['admin_id'];
    $message = $_POST['message'];
    $sender = "Адміністратор";

    $stmt = $conn->prepare("INSERT INTO messages (admin_id, sender, message) VALUES (?, ?, ?)");
    $stmt->bind_param("iss", $admin_id, $sender, $message);
    
    if ($stmt->execute()) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => $stmt->error]);
    }
    
    $stmt->close();
    $conn->close();
}
?>