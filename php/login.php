<?php
header("Content-Type: text/html; charset=utf-8");
session_start();

$request = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
$file = $_SERVER['DOCUMENT_ROOT'] . $request;

if (!file_exists($file) && !is_dir($file)) {
    http_response_code(404);
    include '404.html';
    exit;
}

// Подключение к базе InfinityFree
$servername = "sql211.infinityfree.com";
$username = "if0_40139266";
$password = "Gfo8FVb3NNnLh";
$dbname = "if0_40139266_osbb";

$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Помилка з'єднання: " . $conn->connect_error);
}

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    // Prepared statement для безопасного поиска администратора
    $stmt = $conn->prepare("SELECT id, password, address, maps_link FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashedPassword, $address, $maps_link);
        $stmt->fetch();

        if (password_verify($pass, $hashedPassword)) {
            // Успешный вход
            $_SESSION['admin_id'] = $id;
            $_SESSION['address'] = $address;
            $_SESSION['maps_link'] = $maps_link;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "❌ Невірний пароль";
        }
    } else {
        $error = "❌ Користувача не знайдено";
    }

    $stmt->close();
}

$conn->close();
?>

<!DOCTYPE html>
<html lang="uk">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="author" content="kiwij">
  <meta name="description" content="Вхід адміністратора ОСББ">
  <meta name="keywords" content="ОСББ, вхід, адміністратор, будинок, адреса">
  <link rel="shortcut icon" type="image/x-icon" href="../img/osbb2.png">
  <title>Вхід адміністратора ОСББ</title>
  <link rel="stylesheet" type="text/css" href="../css/style.css">
  <link rel="stylesheet" type="text/css" href="../css/reg_log_adapt.css">
</head>
<body>
  <header class="header">
    <div class="header-center">
      <h1>Вхід адміністратора ОСББ</h1>
    </div>
  </header>

  <main>
    <div class="auth-container">
      <div class="auth-box">
        <h2>Увійти</h2>
        <form action="login.php" method="POST">
          <input type="email" name="email" placeholder="Email" required>
          <input type="password" name="password" placeholder="Пароль" required>
          <button type="submit">Увійти</button>
        </form>
 			<p>Немає акаунта? <a href="/subscribe.html">Придбати</a></p>
<!--        <p>Ще не зареєстровані? <a href="../register.html">Зареєструватися</a></p> -->
      </div>
    </div>
  </main>
</body>
</html>