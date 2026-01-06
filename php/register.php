<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

$servername = "sql211.infinityfree.com";
$username = "if0_40139266";
$password = "Gfo8FVb3NNnLh";
$dbname = "if0_40139266_osbb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Помилка з'єднання: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $fullname = $_POST['fullname'];
    $email = $_POST['email'];
    $password = password_hash($_POST['password'], PASSWORD_DEFAULT);
    $address = $_POST['address'];
    $maps_link = $_POST['maps_link'];

    // Проверка, существует ли уже email
    $checkStmt = $conn->prepare("SELECT id FROM admins WHERE email = ?");
    $checkStmt->bind_param("s", $email);
    $checkStmt->execute();
    $checkStmt->store_result();

    if ($checkStmt->num_rows > 0) {
        echo "Користувач з таким email вже існує.";
    } else {
        // Вставка нового администратора
        $stmt = $conn->prepare("INSERT INTO admins (fullname, email, password, address, maps_link) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("sssss", $fullname, $email, $password, $address, $maps_link);

        if ($stmt->execute()) {
            header("Location: ../login.html");
            exit();
        } else {
            echo "Помилка: " . $stmt->error;
        }
        $stmt->close();
    }
    $checkStmt->close();
}

$conn->close();
?>
