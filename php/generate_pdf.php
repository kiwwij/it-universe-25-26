<?php
// Вимикаємо вивід помилок у потік, щоб не псувати PDF файл
ini_set('display_errors', 0);
error_reporting(E_ALL);
date_default_timezone_set('Europe/Kyiv');

require_once "db_connect.php";

// Встановлюємо кодування для коректного читання кирилиці з БД
$conn->set_charset("utf8mb4");

// Шлях до бібліотеки Dompdf згідно з вашою структурою
$dompdf_path = __DIR__ . '/dompdf/autoload.inc.php';

if (!file_exists($dompdf_path)) {
    die("Помилка: Не знайдено Dompdf. Перевірте наявність папки php/dompdf/");
}

require_once $dompdf_path;

use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $filter = $_POST['filter_type'] ?? 'all';
    $selected_cols = $_POST['cols'] ?? [];

    // Запит до бази даних з урахуванням ваших назв колонок
    $sql = "SELECT apartment, name, entrance, floor, area, currentBalance, debt FROM residents";
    if ($filter === 'debtors') {
        $sql .= " WHERE debt > 0 OR currentBalance < 0";
    }
    
    $result = $conn->query($sql);

    // Створюємо HTML-структуру звіту
    $html = '
    <html>
    <head>
        <meta http-equiv="Content-Type" content="text/html; charset=utf-8"/>
        <style>
            body { font-family: "DejaVu Sans", sans-serif; font-size: 11px; color: #333; }
            table { width: 100%; border-collapse: collapse; margin-top: 15px; }
            th, td { border: 1px solid #444; padding: 6px; text-align: left; }
            th { background-color: #166534; color: white; font-weight: bold; }
            h2 { text-align: center; color: #166534; margin-bottom: 5px; }
            .footer { text-align: right; font-size: 9px; margin-top: 10px; color: #777; }
            .summary { margin-bottom: 10px; font-weight: bold; }
        </style>
    </head>
    <body>
        <h2>Звіт мешканців ОСББ</h2>
        <div class="summary">
            Категорія: ' . ($filter === 'all' ? 'Усі мешканці' : 'Боржники') . '<br>
            Дата: ' . date("d.m.Y H:i") . '
        </div>
        <table>
            <thead>
                <tr>';
    
    // Динамічне формування заголовків таблиці
    if (in_array('apartment', $selected_cols))      $html .= '<th>Кв.</th>';
    if (in_array('name', $selected_cols))           $html .= '<th>ПІБ мешканця</th>';
    if (in_array('entrance', $selected_cols))       $html .= '<th>Під.</th>';
    if (in_array('floor', $selected_cols))          $html .= '<th>Поверх</th>';
    if (in_array('area', $selected_cols))           $html .= '<th>Площа</th>';
    if (in_array('currentBalance', $selected_cols)) $html .= '<th>Баланс</th>';
    if (in_array('debt', $selected_cols))           $html .= '<th>Борг</th>';
    
    $html .= '</tr></thead><tbody>';

    if ($result && $result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $html .= '<tr>';
            if (in_array('apartment', $selected_cols))      $html .= '<td>' . $row['apartment'] . '</td>';
            if (in_array('name', $selected_cols))           $html .= '<td>' . htmlspecialchars($row['name']) . '</td>';
            if (in_array('entrance', $selected_cols))       $html .= '<td>' . $row['entrance'] . '</td>';
            if (in_array('floor', $selected_cols))          $html .= '<td>' . $row['floor'] . '</td>';
            if (in_array('area', $selected_cols))           $html .= '<td>' . $row['area'] . ' м²</td>';
            if (in_array('currentBalance', $selected_cols)) $html .= '<td>' . $row['currentBalance'] . ' грн</td>';
            if (in_array('debt', $selected_cols))           $html .= '<td>' . $row['debt'] . ' грн</td>';
            $html .= '</tr>';
        }
    } else {
        $html .= '<tr><td colspan="20" style="text-align:center;">Даних не знайдено</td></tr>';
    }

    $html .= '</tbody></table>
        <div class="footer">Сгенеровано системою адміністрування ОСББ &copy; ' . date("Y") . '</div>
    </body>
    </html>';

    // Налаштування Dompdf
    $options = new Options();
    $options->set('isHtml5ParserEnabled', true);
    $options->set('defaultFont', 'DejaVu Sans'); // Обов'язково для кирилиці
    
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);

    // Якщо обрано більше 5 колонок — робимо лист альбомним (landscape)
    $orientation = (count($selected_cols) > 5) ? 'landscape' : 'portrait';
    $dompdf->setPaper('A4', $orientation);
    
    $dompdf->render();

    // Очищуємо буфер виводу, щоб PDF не пошкодився випадковими символами
    if (ob_get_length()) ob_end_clean();

    // Відправляємо файл клієнту
    $dompdf->stream("zvit_osbb_" . date("d_m_Y") . ".pdf", array("Attachment" => 1));
    exit();
}