<?php
// Встановлюємо заголовки для JSON та кодування
header("Content-Type: application/json; charset=utf-8");
session_start();

// 1. Перевірка авторизації
if (!isset($_SESSION['admin_id'])) {
    echo json_encode(["error" => "Доступ заборонено"]);
    exit;
}

$admin_id = $_SESSION['admin_id'];

// Підключення до бази
$servername = "sql211.infinityfree.com";
$username = "if0_40139266";
$password = "Gfo8FVb3NNnLh";
$dbname = "if0_40139266_osbb";

$conn = new mysqli($servername, $username, $password, $dbname);

// 2. Виправлення ієрогліфів (UTF-8)
$conn->set_charset("utf8mb4");

if ($conn->connect_error) {
    die(json_encode(["error" => $conn->connect_error]));
}

// 3. Вибірка жителів ТІЛЬКИ для поточного адміністратора
// Використовуємо підготовлений запит для безпеки
$stmt = $conn->prepare("SELECT id, name, apartment, entrance, floor, area, currentBalance, paymentDue, debt FROM residents WHERE admin_id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$residents = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Якщо квартира порожня в БД
        if ($row['name'] === '' || $row['name'] === '—' || $row['name'] === null) {
            $row['name'] = null;
        }
        $residents[] = $row;
    }
}

// Повертаємо дані у форматі JSON з підтримкою кирилиці
echo json_encode($residents, JSON_UNESCAPED_UNICODE);

$stmt->close();
$conn->close();
?>