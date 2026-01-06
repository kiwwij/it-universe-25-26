<?php
// 1. Увімкнення відображення помилок
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

header("Content-Type: text/html; charset=utf-8");
session_start();

// 2. Перевірка сесії
if (!isset($_SESSION['admin_id'])) {
    header("Location: ../login.html");
    exit();
}

// 3. Підключення до БД
require_once "db_connect.php";
$conn->set_charset("utf8mb4");

$admin_id = $_SESSION['admin_id'];

// 4. Отримуємо дані адміністратора для шапки
$address = "Адреса не вказана";
$maps_link = "#";

$stmt = $conn->prepare("SELECT address, maps_link FROM admins WHERE id = ?");
if ($stmt) {
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $result = $stmt->get_result();
    if ($row = $result->fetch_assoc()) {
        $address = htmlspecialchars($row['address']);
        $maps_link = htmlspecialchars($row['maps_link']);
    }
    $stmt->close();
}

// 5. Отримання даних для діаграми (квартира та борг)
$apartments = [];
$debts = [];
$chartQuery = $conn->query("SELECT apartment, debt FROM residents WHERE admin_id = '$admin_id' ORDER BY CAST(apartment AS UNSIGNED) ASC");
while ($row = $chartQuery->fetch_assoc()) {
    $apartments[] = $row['apartment'];
    $debts[] = (float)$row['debt'];
}
?>

<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link rel="shortcut icon" type="image/x-icon" href="../img/osbb2.png">
    <title>Звітність та аналітика ОСББ | <?= $address ?></title>
    <link rel="stylesheet" href="../css/style.css">
    <link rel="stylesheet" href="../css/adaptation.css">
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-theme' : '' ?>">
  
  <header class="header">
    <div class="header-left">
      <span class="address">
        <a href="<?= $maps_link ?>" target="_blank"><?= $address ?></a>
      </span>
    </div>
    <div class="header-center">
      <img class="logo" src="../img/osbb3.png" alt="Логотип ОСББ">
      <h1>Звітність та Аналітика</h1>
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
    <section class="chart-container">
      <h2>Візуалізація заборгованості</h2>
      <canvas id="debtChart" style="width: 100%; max-height: 400px;"></canvas>
    </section>

    <section>
      <h2><img src="../img/megaphone.png" alt="Звіт" style="height: 24px; vertical-align: middle; margin-right: 8px;">Експорт у PDF</h2>
      
      <form action="generate_pdf.php" method="post">
        <p><strong>1. Категорія мешканців:</strong></p>
        <div class="select-wrapper" style="width: 100%;">
          <select name="filter_type" required>
            <option value="all">Усі мешканці</option>
            <option value="debtors">Тільки боржники</option>
          </select>
        </div>

        <p style="margin-top: 25px;"><strong>2. Дані для документа:</strong></p>
        <div class="report-options">
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="apartment" checked> № Квартири</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="name" checked> ПІБ мешканця</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="entrance"> Під'їзд</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="floor"> Поверх</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="area"> Площа (м²)</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="currentBalance" checked> Баланс</label>
          <label class="checkbox-item"><input type="checkbox" name="cols[]" value="debt" checked> Сума боргу</label>
        </div>

        <button type="submit" class="full-width-btn">Завантажити PDF</button>
      </form>
    </section>
  </main>

  <footer class="footer">
    <div class="footer-container">
      <p>&copy; 2025 ОСББ | Аналітика та звіти</p>
      <div class="footer-links">
        <a href="logout.php">Вихід</a>
        <a href="../index.php">Головна</a>
      </div>
    </div>
  </footer>

  <script>
    // Отримуємо дані з PHP
    const apartmentLabels = <?php echo json_encode($apartments); ?>;
    const debtValues = <?php echo json_encode($debts); ?>;
    let debtChart;

    function renderChart() {
        const ctx = document.getElementById('debtChart').getContext('2d');
        const isDark = document.body.classList.contains('dark-theme');
        const gridColor = isDark ? 'rgba(255, 255, 255, 0.1)' : 'rgba(0, 0, 0, 0.1)';
        const textColor = isDark ? '#e5e5e5' : '#1c1c1c';

        if (debtChart) {
            debtChart.destroy(); // Очищуємо старий графік перед перемальовуванням
        }

        debtChart = new Chart(ctx, {
            type: 'bar',
            data: {
                labels: apartmentLabels,
                datasets: [{
                    label: 'Заборгованість (грн)',
                    data: debtValues,
                    backgroundColor: 'rgba(220, 38, 38, 0.6)',
                    borderColor: 'rgba(220, 38, 38, 1)',
                    borderWidth: 1
                }]
            },
            options: {
                responsive: true,
                scales: {
                    y: {
                        beginAtZero: true,
                        grid: { color: gridColor },
                        ticks: { color: textColor }
                    },
                    x: {
                        grid: { color: gridColor },
                        ticks: { color: textColor, autoSkip: false }
                    }
                },
                plugins: {
                    legend: {
                        labels: { color: textColor }
                    }
                }
            }
        });
    }

    // Логіка перемикання теми
    const themeToggle = document.getElementById('themeToggle');
    const checkbox = themeToggle.querySelector('input');

    themeToggle.addEventListener('change', () => {
        if (checkbox.checked) {
            document.body.classList.add('dark-theme');
            document.cookie = "theme=dark; path=/; max-age=" + (30 * 24 * 60 * 60);
        } else {
            document.body.classList.remove('dark-theme');
            document.cookie = "theme=light; path=/; max-age=" + (30 * 24 * 60 * 60);
        }
        renderChart(); // Оновлюємо графік під нову тему
    });

    // Початковий рендер
    renderChart();
  </script>
  <script src="../js/script.js"></script>
</body>
</html>