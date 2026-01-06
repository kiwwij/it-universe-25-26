<?php
// 1. ПРИМУСОВЕ НАЛАШТУВАННЯ КОДУВАННЯ PHP
mb_internal_encoding("UTF-8");
header("Content-Type: application/json; charset=utf-8");

// Вимикаємо вивід помилок у потік, щоб не зіпсувати JSON
ini_set('display_errors', 0);
error_reporting(E_ALL);

session_start();

// 2. ПЕРЕВІРКА АВТОРИЗАЦІЇ
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(['success' => false, 'error' => 'Сесія завершена. Увійдіть знову']);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// 3. ПІДКЛЮЧЕННЯ ДО БД
$servername = "sql211.infinityfree.com";
$username = "if0_40139266";
$password = "Gfo8FVb3NNnLh";
$dbname = "if0_40139266_osbb"; 

$conn = new mysqli($servername, $username, $password, $dbname);

// КРИТИЧНО ВАЖЛИВО: Встановлення кодування з'єднання
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    echo json_encode(['success' => false, 'error' => 'Помилка підключення до БД']);
    exit;
}

// 4. ОБРОБКА ДАНИХ
$input = json_decode(file_get_contents('php://input'), true);
if (!$input || !isset($input['name'])) {
    echo json_encode(['success' => false, 'error' => 'Дані не отримано']);
    exit;
}

// Очищення даних від зайвих пробілів
$name = trim($input['name']);
$apartment = (int)$input['apartment'];
$entrance = (int)$input['entrance'];
$balance = (float)$input['currentBalance'];

// 5. ПЕРЕВІРКА КВАРТИРИ
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

// 6. ОНОВЛЕННЯ ДАНИХ
// Використовуємо підготовлений запит для захисту від ієрогліфів та ін'єкцій
$stmt = $conn->prepare("UPDATE residents SET name = ?, entrance = ?, currentBalance = ? WHERE apartment = ? AND admin_id = ?");
$stmt->bind_param("sidii", $name, $entrance, $balance, $apartment, $admin_id);

if ($stmt->execute()) {
    echo json_encode(['success' => true]);
} else {
    echo json_encode(['success' => false, 'error' => 'Помилка бази даних: ' . $conn->error]);
}

$stmt->close();
$conn->close();