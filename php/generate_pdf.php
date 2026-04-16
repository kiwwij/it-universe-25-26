<?php
date_default_timezone_set('Europe/Kyiv');
session_start();

if (!isset($_SESSION['admin_id'])) {
    die("Помилка: Немає доступу. Увійдіть в систему.");
}
$admin_id = (int)$_SESSION['admin_id'];

require_once "db_connect.php";
$conn->set_charset("utf8mb4");

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = $_POST['filter_type'] ?? 'all';
    $selected_cols = $_POST['cols'] ?? [];

    $sql = "SELECT apartment, name, entrance, floor, area, currentBalance, debt FROM residents WHERE admin_id = $admin_id";
    if ($filter === 'debtors') {
        $sql .= " AND (debt > 0 OR currentBalance < 0)";
    }
    
    $result = $conn->query($sql);
    
    $categoryText = ($filter === 'all') ? 'Усі мешканці' : 'Боржники';
    $dateText = date("d.m.Y H:i");
?>
<!DOCTYPE html>
<html lang="uk">
<head>
    <meta charset="UTF-8">
    <title>Звіт ОСББ_<?= date("d_m_Y") ?></title>
    <style>
        body { font-family: "Segoe UI", Arial, sans-serif; font-size: 13px; color: #333; padding: 20px; }
        .container { max-width: 1000px; margin: 0 auto; }
        table { width: 100%; border-collapse: collapse; margin-top: 15px; }
        th, td { border: 1px solid #ccc; padding: 8px; text-align: left; }
        th { background-color: #166534 !important; color: white !important; font-weight: bold; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        h2 { text-align: center; color: #166534; margin-bottom: 5px; }
        .summary { margin-bottom: 15px; font-weight: bold; }
        .btn { padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; font-weight: bold; color: white; }
        .btn-print { background-color: #166534; }
        .btn-back { background-color: #666; margin-left: 10px; }
        
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
            @page { margin: 1cm; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="no-print" style="text-align: right; margin-bottom: 20px;">
            <button class="btn btn-print" onclick="window.print()">🖨️ Зберегти як PDF / Друк</button>
            <button class="btn btn-back" onclick="window.history.back()">⬅ Повернутися</button>
        </div>

        <h2>Звіт мешканців ОСББ</h2>
        <div class="summary">
            Категорія: <?= $categoryText ?><br>
            Дата формування: <?= $dateText ?>
        </div>

        <table>
            <thead>
                <tr>
                    <?php if (in_array('apartment', $selected_cols)) echo '<th>Кв.</th>'; ?>
                    <?php if (in_array('name', $selected_cols)) echo '<th>ПІБ мешканця</th>'; ?>
                    <?php if (in_array('entrance', $selected_cols)) echo '<th>Під.</th>'; ?>
                    <?php if (in_array('floor', $selected_cols)) echo '<th>Поверх</th>'; ?>
                    <?php if (in_array('area', $selected_cols)) echo '<th>Площа</th>'; ?>
                    <?php if (in_array('currentBalance', $selected_cols)) echo '<th>Баланс</th>'; ?>
                    <?php if (in_array('debt', $selected_cols)) echo '<th>Борг</th>'; ?>
                </tr>
            </thead>
            <tbody>
                <?php if ($result && $result->num_rows > 0): ?>
                    <?php while ($row = $result->fetch_assoc()): ?>
                        <tr>
                            <?php if (in_array('apartment', $selected_cols)) echo '<td>' . $row['apartment'] . '</td>'; ?>
                            <?php if (in_array('name', $selected_cols)) echo '<td>' . htmlspecialchars($row['name'] ?? '') . '</td>'; ?>
                            <?php if (in_array('entrance', $selected_cols)) echo '<td>' . $row['entrance'] . '</td>'; ?>
                            <?php if (in_array('floor', $selected_cols)) echo '<td>' . $row['floor'] . '</td>'; ?>
                            <?php if (in_array('area', $selected_cols)) echo '<td>' . $row['area'] . ' м²</td>'; ?>
                            <?php if (in_array('currentBalance', $selected_cols)) echo '<td>' . $row['currentBalance'] . ' ₴</td>'; ?>
                            <?php if (in_array('debt', $selected_cols)) echo '<td>' . $row['debt'] . ' ₴</td>'; ?>
                        </tr>
                    <?php endwhile; ?>
                <?php else: ?>
                    <tr><td colspan="10" style="text-align:center;">Даних не знайдено</td></tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

    <script>
        window.onload = function() {
            setTimeout(function() {
                window.print();
            }, 500);
        };
    </script>
</body>
</html>
<?php } ?>