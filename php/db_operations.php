<?php
// 1. НАЛАШТУВАННЯ ТА БЕЗПЕКА
session_start();
header("Content-Type: text/html; charset=utf-8");

if (!isset($_SESSION['admin_id'])) {
    http_response_code(403);
    exit('Доступ заборонено');
}

$admin_id = $_SESSION['admin_id'];

// 2. ПІДКЛЮЧЕННЯ
require_once "db_connect.php"; 
$conn->set_charset("utf8mb4"); 

if ($conn->connect_error) die("Помилка з’єднання: " . $conn->connect_error);

$action = $_REQUEST['action'] ?? '';
$table = $_REQUEST['table'] ?? '';

// --- КІНЕЦЬ ЗАГАЛЬНИХ НАЛАШТУВАНЬ ---

// 3. ЗАВАНТАЖЕННЯ ТАБЛИЦІ
if ($action === 'load' && $table === 'residents') {
    $stmt = $conn->prepare("SELECT * FROM `$table` WHERE admin_id = ?");
    $stmt->bind_param("i", $admin_id);
    $stmt->execute();
    $res = $stmt->get_result();

    if (!$res) { echo "Помилка завантаження даних"; exit; }

    echo "<table><thead><tr>";
    $fields = $res->fetch_fields();
    foreach ($fields as $field) {
        if ($field->name === 'admin_id') continue; 
        echo "<th>{$field->name}</th>";
    }
    echo "<th>Дії</th></tr></thead><tbody>";

    while ($row = $res->fetch_assoc()) {
        echo "<tr id='row_{$row['id']}'>";
        foreach ($row as $k => $v) {
            if ($k === 'admin_id') continue; 
            echo "<td><input name='$k' value='" . htmlspecialchars($v, ENT_QUOTES) . "'></td>";
        }
        echo "<td>
              <button onclick=\"editRow('$table', '{$row['id']}')\">💾</button>
              <button onclick=\"deleteRow('$table', '{$row['id']}')\">🗑️</button>
              </td></tr>";
    }

    echo "</tbody></table><h3>➕ Додати запис</h3><form id='addForm'>";
    $desc = $conn->query("DESCRIBE `$table`");
    while ($f = $desc->fetch_assoc()) {
        if ($f['Extra'] !== 'auto_increment' && $f['Field'] !== 'admin_id') {
            echo "<input name='{$f['Field']}' placeholder='{$f['Field']}'>";
        }
    }
    echo "</form><button onclick=\"addRow('$table')\">Додати</button>";
    $stmt->close();
    exit; // Важливо вийти, щоб не виконувався код нижче
}

// 4. ОНОВЛЕННЯ (UPDATE)
if ($action === 'update') {
    $id = intval($_POST['id']);
    // Видаляємо службові поля з масиву POST, щоб вони не потрапили в SET
    unset($_POST['action'], $_POST['table'], $_POST['id']);
    
    $set = [];
    foreach ($_POST as $k => $v) {
        $set[] = "`$k`='" . $conn->real_escape_string($v) . "'";
    }
    
    // Фільтрація за ID запису ТА ID адміна для безпеки
    $sql = "UPDATE `$table` SET " . implode(",", $set) . " WHERE id=$id AND admin_id=$admin_id";
    echo $conn->query($sql) ? "✅ Збережено!" : "❌ Помилка оновлення!";
    exit;
}

// 5. ВИДАЛЕННЯ (DELETE)
if ($action === 'delete') {
    $id = intval($_GET['id']);
    // Видалення дозволено тільки якщо запис належить цьому адміну
    $sql = "DELETE FROM `$table` WHERE id=$id AND admin_id=$admin_id";
    echo $conn->query($sql) ? "🗑️ Видалено!" : "❌ Помилка видалення!";
    exit;
}

// 6. ДОДАВАННЯ (Насправді - заселення у вільну квартиру)
if ($action === 'add') {
    $table = $_REQUEST['table'] ?? '';
    $apt = intval($_POST['apartment'] ?? 0);
    $name = $conn->real_escape_string($_POST['name'] ?? '');
    $balance = floatval($_POST['currentBalance'] ?? 0);

    // 1. Перевірка номера квартири (1-54)
    if ($apt < 1 || $apt > 54) {
        echo "❌ Помилка: Номер квартири має бути від 1 до 54";
        exit;
    }

    // 2. Перевірка, чи не зайнята вона вже іншим адміністратором або мешканцем
    $check = $conn->query("SELECT name FROM `$table` WHERE apartment = $apt AND admin_id = $admin_id");
    $row = $check->fetch_assoc();

    if ($row && !empty($row['name']) && $row['name'] !== '—') {
        echo "❌ Помилка: Квартира №$apt вже зайнята!";
        exit;
    }

    // 3. Визначаємо під'їзд (1-22: 1-й, 23-54: 2-й)
    $entrance = ($apt <= 22) ? 1 : 2;

    // 4. Оновлюємо дані існуючого рядка замість INSERT
    $sql = "UPDATE `$table` SET 
            name = '$name', 
            currentBalance = $balance, 
            entrance = $entrance,
            debt = " . ($balance < 0 ? abs($balance) : 0) . "
            WHERE apartment = $apt AND admin_id = $admin_id";

    if ($conn->query($sql)) {
        echo "✅ Мешканця успішно заселено у квартиру №$apt!";
    } else {
        echo "❌ Помилка бази даних: " . $conn->error;
    }
    exit;
}