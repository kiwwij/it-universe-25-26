<?php
// 1. НАЛАШТУВАННЯ
mb_internal_encoding("UTF-8"); 
header("Content-Type: application/json; charset=utf-8");
session_start();

// 2. ПЕРЕВІРКА АВТОРИЗАЦІЇ
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    echo json_encode(['success' => false, 'error' => 'Ви не авторизовані']);
    exit();
}

$admin_id = $_SESSION['admin_id'];

// 3. ПІДКЛЮЧЕННЯ ДО БД
require_once "db_connect.php"; // Використовуємо існуюче підключення
$conn->set_charset("utf8mb4");

// 4. ОТРИМАННЯ ПОВІДОМЛЕНЬ
$stmt = $conn->prepare("SELECT id, message, sender, created_at FROM messages WHERE admin_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$messages = [];
while($row = $result->fetch_assoc()) {
    $messages[] = $row;
}

// 5. ВІДПРАВКА JSON
echo json_encode($messages, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>