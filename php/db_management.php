<?php
// 1. Увімкнення відображення помилок та сесія
ini_set('display_errors', 1);
error_reporting(E_ALL);
session_start();
header("Content-Type: text/html; charset=utf-8");

// 2. Перевірка авторизації
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.html");
    exit();
}

require_once "db_connect.php";
$admin_id = $_SESSION['admin_id'];
$conn->set_charset("utf8mb4");

// 3. Отримуємо адресу для хедеру
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
  <link rel="stylesheet" href="../css/style.css"> <link rel="shortcut icon" type="image/x-icon" href="../img/osbb2.png">
  <style>
      .action-buttons { display: flex; gap: 5px; justify-content: center; }
      table input { width: 100%; box-sizing: border-box; }
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
      
      <p style="text-align: center; font-size: 0.9em; color: #666;">Змінюйте значення прямо в таблиці та натискайте 💾 для збереження</p>
      
      <div id="tableContainer" class="table-wrapper">
        <p style="text-align:center;">Завантаження даних...</p>
      </div>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <p>&copy; 2025 ОСББ | Панель управління</p>
      <div class="footer-links">
        <a href="logout.php">Вихід</a>
        <a href="../index.php">Головна</a>
      </div>
    </div>
  </footer>

  <script>
    // --- Логіка перемикання теми ---
    const themeToggle = document.getElementById('themeToggle');
    const themeCheckbox = themeToggle.querySelector('input');

    themeToggle.addEventListener('change', () => {
        if (themeCheckbox.checked) {
            document.body.classList.add('dark-theme');
            document.cookie = "theme=dark; path=/; max-age=" + (30 * 24 * 60 * 60);
        } else {
            document.body.classList.remove('dark-theme');
            document.cookie = "theme=light; path=/; max-age=" + (30 * 24 * 60 * 60);
        }
    });

    // --- Функції керування БД ---
    async function loadTable() {
        try {
            const response = await fetch('db_operations.php?action=load&table=residents');
            const html = await response.text();
            document.getElementById('tableContainer').innerHTML = html;
        } catch (e) {
            document.getElementById('tableContainer').innerHTML = "<p class='alert error'>Помилка завантаження</p>";
        }
    }

    async function editRow(table, id) {
        const row = document.getElementById('row_' + id);
        const inputs = row.querySelectorAll('input');
        const formData = new FormData();
        
        formData.append('action', 'update');
        formData.append('table', table);
        formData.append('id', id);

        inputs.forEach(input => {
            formData.append(input.name, input.value);
        });

        const response = await fetch('db_operations.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.text();
        alert(result);
        if(result.includes("✅")) loadTable(); 
    }

    async function deleteRow(table, id) {
        if (!confirm('Видалити цей запис безповоротно?')) return;

        const response = await fetch(`db_operations.php?action=delete&table=${table}&id=${id}`);
        const result = await response.text();
        alert(result);
        loadTable();
    }

    async function addRow(table) {
    const form = document.getElementById('addForm');
    if(!form) return;

    // Отримуємо значення квартири з форми
    const formData = new FormData(form);
    const apartment = formData.get('apartment');

    // Валідація на стороні клієнта для швидкості
    if (apartment) {
        const aptNum = parseInt(apartment);
        if (aptNum < 1 || aptNum > 54) {
            alert("❌ Номер квартири повинен бути від 1 до 54");
            return;
        }
    }

    formData.append('action', 'add');
    formData.append('table', table);

    try {
        const response = await fetch('db_operations.php', {
            method: 'POST',
            body: formData
        });
        const result = await response.text();
        
        // Виводимо повідомлення від сервера (там тепер теж є перевірки)
        alert(result);
        
        if (result.includes("✅")) {
            loadTable();
        }
    } catch (e) {
        alert("❌ Помилка відправки даних");
    }
}

    window.onload = loadTable;
  </script>
  <script src="../js/script.js"></script>
</body>
</html>