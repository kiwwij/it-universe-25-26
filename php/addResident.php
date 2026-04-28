<?php
mb_internal_encoding("UTF-8");
header("Content-Type: application/json; charset=utf-8");

ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Сесія завершена. Увійдіть знову']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

require_once "db_connect.php";

$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['name'])) {
    echo json_encode(['success' => false, 'error' => 'Дані не отримано']);
    exit;
}

$name = trim($input['name']);
$apartment = (int)$input['apartment'];
$entrance = (int)$input['entrance'];
$balance = (float)$input['currentBalance'];

$stmt_check = $conn->prepare("SELECT name FROM residents WHERE apartment = ? AND admin_id = ?");
$stmt_check->bind_param("ii", $apartment, $admin_id);
$stmt_check->execute();
$res_check = $stmt_check->get_result();
$row = $res_check->fetch_assoc();

if ($row && !empty($row['name'])) {
    echo json_encode(['success' => false, 'error' => 'Квартира №' . $apartment . ' вже зайнята']);
    exit;
}
$stmt_check->close();

$stmt = $conn->prepare("UPDATE residents SET name = ?, entrance = ?, currentBalance = ? WHERE apartment = ? AND admin_id = ?");
$stmt->bind_param("sidii", $name, $entrance, $balance, $apartment, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Помилка бази даних: ' . $conn->error]);
}

$stmt->close();
$conn->close();