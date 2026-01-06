<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

// 1. Перевірка сесії: якщо адмін не увійшов – редирект на login.html
if (!isset($_SESSION['admin_id'])) {
    header("Location: login.html");
    exit();
}

// 2. Підключення до БД
require_once "php/db_connect.php";
// Встановлюємо кодування utf8mb4 для коректного відображення українських літер
$conn->set_charset("utf8mb4");

// Отримуємо ID поточного адміністратора
$admin_id = $_SESSION['admin_id'];

// 3. Запит для вибірки даних поточного адміністратора
$stmt = $conn->prepare("SELECT address, maps_link, fullname FROM admins WHERE id = ?");
$stmt->bind_param("i", $admin_id);
$stmt->execute();
$result = $stmt->get_result();

$address = "Адреса не вказана";
$maps_link = "#";
$fullname = "Невідомий користувач";

if ($row = $result->fetch_assoc()) {
    $address = htmlspecialchars($row['address']);
    $maps_link = htmlspecialchars($row['maps_link']);
    $fullname = htmlspecialchars($row['fullname']);
}

$stmt->close();
// Закриваємо з'єднання в кінці файлу або після всіх запитів
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="kiwij">
  <meta name="description" content="Адміністрування ОСББ">
  <meta name="keywords" content="ОСББ, адміністрування, квартири, мешканці, баланс, повідомлення">
  <link rel="shortcut icon" type="image/x-icon" href="img/osbb2.png">
  <title>Адмін-панель ОСББ | <?= $address ?></title>
  <link rel="stylesheet" href="css/style.css">
  <link rel="stylesheet" href="css/adaptation.css">
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-theme' : '' ?>">

  <header class="header">
    <div class="header-left">
      <span class="address">
        <a href="<?= $maps_link ?>" target="_blank"><?= $address ?></a>
      </span>
    </div>

    <div class="header-center">
      <img class="logo" src="img/osbb3.png" alt="Логотип ОСББ">
      <h1>Адмін-панель ОСББ</h1>
      <p style="margin: 0; font-size: 14px; opacity: 0.9;">Вітаємо, <?= $fullname ?></p>
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
      <h2><img src="img/owner.png" alt="Мешканець" style="height: 24px; vertical-align: middle; margin-right: 6px;">Додати мешканця</h2>
      <form id="addResidentForm" method="post" accept-charset="UTF-8">
        <input type="hidden" id="admin_id" value="<?= $admin_id ?>">
        
        <input type="text" id="name" placeholder="ПІБ мешканця" required>

        <div class="select-wrapper">
          <select id="apartment" required>
            <option value="">Оберіть вільну квартиру</option>
            </select>
        </div>
        
        <input type="number" id="balance" placeholder="Початковий баланс (грн)" required>
        <button type="submit">Додати в базу</button>
      </form>
    </section>

    <section>
      <h2><img src="img/search.png" alt="Пошук" style="height: 22px; vertical-align: middle; margin-right: 6px;">Пошук мешканця</h2>
      <div style="display: flex; gap: 10px;">
        <input type="number" id="searchId" placeholder="Введіть № квартири" style="flex: 1;">
        <button onclick="searchResident()">Знайти</button>
      </div>
      <p id="searchResult" style="margin-top: 15px; font-weight: bold;"></p>
    </section>

    <section>
      <h2><img src="img/home.png" alt="Мешканці" style="height: 28px; vertical-align: middle; margin-right: 6px;">Ваші мешканці</h2>

      <div id="colorLegend" style="margin-bottom:15px; font-size: 14px; line-height: 1.6;">
        <strong>Колір балансу:</strong>
        <div class="green">● зелений – на рахунку є гроші</div>
        <div class="black">● чорний – баланс 0 грн</div>
        <div class="red">● червоний – заборгованість</div>
      </div>

      <button id="toggleDebt">✅ Показати тільки боржників</button>

      <div style="overflow-x: auto; margin-top: 15px;">
        <table id="residentsTable">
          <thead>
            <tr>
              <th onclick="sortTable(0)">ID</th>
              <th onclick="sortTable(1)">ПІБ</th>
              <th onclick="sortTable(2)">Квартира</th>
              <th onclick="sortTable(3)">Під’їзд</th>
              <th onclick="sortTable(4)">Площа м²</th>
              <th onclick="sortTable(5)">До сплати</th>
              <th onclick="sortTable(6)" class="debt-col" style="display:none;">Заборгованість</th>
            </tr>
          </thead>
          <tbody>
            </tbody>
        </table>
      </div>
    </section>

    <section>
      <h2><img src="img/megaphone.png" alt="Повідомлення" style="height: 24px; vertical-align: middle; margin-right: 6px;">Дошка оголошень</h2>
      <textarea id="adminMessage" placeholder="Введіть текст оголошення для мешканців..."></textarea><br>
      <button type="button" id="sendMessageBtn" onclick="sendMessage()">Опублікувати</button>
      <ul id="messages" style="margin-top: 20px;"></ul>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <p>&copy; 2025 ОСББ</p>
      <div class="footer-links">
        <a href="php/login.php">Вихід</a>
        <a href="php/db_management.php">Керування БД</a>
        <a href="php/reports.php">Аналітика та звіти</a>
      </div>
    </div>
  </footer>

  <script src="js/script.js"></script>
</body>
</html>
<?php $conn->close(); ?>