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

require_once "db_connect.php";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $email = $_POST['email'];
    $pass = $_POST['password'];

    $stmt = $conn->prepare("SELECT id, password, address, maps_link FROM admins WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    $stmt->store_result();

    if ($stmt->num_rows == 1) {
        $stmt->bind_result($id, $hashedPassword, $address, $maps_link);
        $stmt->fetch();

        if (password_verify($pass, $hashedPassword)) {
            $_SESSION['admin_id'] = $id;
            $_SESSION['address'] = $address;
            $_SESSION['maps_link'] = $maps_link;
            header("Location: ../index.php");
            exit();
        } else {
            $error = "<i class='bx bx-error-circle'></i> Невірний пароль";
        }
    } else {
        $error = "<i class='bx bx-user-x'></i> Користувача не знайдено";
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
  <link rel="shortcut icon" type="image/x-icon" href="../img/osbb2.png">
  <title>Вхід адміністратора ОСББ</title>
  <link rel="stylesheet" type="text/css" href="../css/style.css">
  <link rel="stylesheet" type="text/css" href="../css/reg_log_adapt.css">
  <link href='https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css' rel='stylesheet'>
</head>

<body class="<?= isset($_COOKIE['theme']) && $_COOKIE['theme'] === 'dark' ? 'dark-theme' : '' ?>">
  
  <header class="header">
    <div class="header-left"></div>
    <div class="header-center">
      <h1>Вхід адміністратора ОСББ</h1>
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
    <div class="auth-container">
      <div class="auth-box">
        <h2>Увійти</h2>
        
        <?php if (!empty($error)): ?>
            <p class="error-msg"><?php echo $error; ?></p>
        <?php endif; ?>

        <form action="login.php" method="POST">
          <input type="email" name="email" placeholder="Email" required>
          
          <div class="password-container">
            <input type="password" name="password" id="password" placeholder="Пароль" required>
            <i class='bx bx-show toggle-password' id="togglePassword"></i>
          </div>

          <button type="submit">Увійти</button>
        </form>
 		<p>Немає акаунта? <a href="/subscribe.html">Придбати</a></p>
      </div>
    </div>
  </main>

  <script>
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
      const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
      password.setAttribute('type', type);
      this.classList.toggle('bx-show');
      this.classList.toggle('bx-hide');
    });

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
    });
  </script>
</body>
</html>