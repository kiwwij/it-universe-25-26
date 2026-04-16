<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Not authorized');
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['id'])) {
    $id = intval($_POST['id']);

    // $conn = new mysqli(
    //     "sql211.infinityfree.com", 
    //     "if0_40139266",           
    //     "Gfo8FVb3NNnLh",           
    //     "if0_40139266_osbb"        
    // );

    // if ($conn->connect_error) die("Помилка з'єднання: " . $conn->connect_error);

    require_once "db_connect.php";

    $stmt = $conn->prepare("DELETE FROM messages WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();

    $stmt->close();
    $conn->close();
}
?>
