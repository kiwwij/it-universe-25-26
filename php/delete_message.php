<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

// Проверка сессии администратора
if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Not authorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);

    // Подключение к базе InfinityFree
    $conn = new mysqli(
        "sql211.infinityfree.com", // MySQL Hostname
        "if0_40139266",            // MySQL User
        "Gfo8FVb3NNnLh",           // Пароль vPanel
        "if0_40139266_osbb"        // Имя базы данных
    );

    if ($conn->connect_error) die("Помилка з'єднання: " . $conn->connect_error);

    // Подготовленное выражение для удаления
    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}
?>
