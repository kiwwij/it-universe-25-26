<?php
$host = "sql211.byetcluster.com";
$user = "if0_40139266";
$pass = "Gfo8FVb3NNnLh";
$dbname = "if0_40139266_osbb";

$conn = new mysqli($host, $user, $pass, $dbname);
if ($conn->connect_error) {
    die("Помилка підключення до БД: " . $conn->connect_error);
}

$conn->set_charset("utf8mb4");
?>
