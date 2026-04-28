<?php
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header("Content-Type: text/html; charset=utf-8");

if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.html");
    exit();
}

require_once "db_connect.php";
$admin_id = $_SESSION['admin_id'];
$conn->set_charset("utf8mb4");

$stmt = $conn->prepare("SELECT address FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$res = $stmt->get_result();
$admin_data = $res->fetch_assoc();
$address = $admin_data['address'] ?? "Адреса не вказана";
$stmt->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Керування базою даних | ОСББ</title>
  <link rel="stylesheet" href="../css/db_management.css">
  <link rel="stylesheet" href="../css/style.css"> 
  <link rel="shortcut icon" type="image/x-icon" href="../img/osbb2.png">
  <style>
      .action-buttons { display: flex; gap: 5px; justify-content: center; }
      .table-wrapper { overflow-x: auto; margin-top: 15px; }
  </style>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-theme' : '' ?>">

  <header class="header">
    <div class="header-left">
      <span class="address"><?= htmlspecialchars($address) ?></span>
    </div>
    <div class="header-center">
      <img class="logo" src="../img/osbb3.png" alt="Логотип">
      <h1>Редагування Бази Даних</h1>
    </div>
    <div class="header-right">
      <label class="ui-switch" id="themeToggle">
        <input type="checkbox" <?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'checked' : '' ?>>
        <div class="slider">
          <div class="circle"></div>
        </div>
      </label>
    </div>
  </header>

  <main>
    <section>
      <h2><img src="../img/home.png" alt="Home" style="height:24px; vertical-align:middle; margin-right:8px;">Мешканці: Швидке редагування</h2>
      <div style="text-align: center; margin-bottom: 15px;">
          <a href="../index.php"><button type="button">⬅ На головну</button></a>
          <button onclick="loadTable()">🔄 Оновити дані</button>
      </div>
      
      <p style="text-align: center; font-size: 0.9em; color: #666;">Змінюйте значення прямо в таблиці та натискайте <b>Enter</b> або 💾 для збереження</p>
      
      <div id="tableContainer" class="table-wrapper">
        <p style="text-align:center;">Завантаження даних...</p>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <p>&copy; <?= date('Y') ?> ОСББ | Панель управління</p>
      <div class="footer-links">
        <a href="logout.php">Вихід</a>
        <a href="../index.php">Головна</a>
        <a href="php/reports.php">Аналітика та звіти</a>
      </div>
    </div>
  </footer>

  <script src="../js/script.js"></script>
  <script>
      window.onload = loadTable;
  </script>
</body>
</html>