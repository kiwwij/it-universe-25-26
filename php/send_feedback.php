<?php
header('Content-Type: application/json; charset=utf-8');
$data = json_decode(file_get_contents('php://input'), true);

if ($data) {
    $to = "kaka5k340ssa@gmail.com";
    
    $name = htmlspecialchars(trim($data['name']));
    $email = htmlspecialchars(trim($data['email']));
    $user_message = htmlspecialchars(trim($data['message']));
    
    $subject = "ОСББ Платформа: Нове повідомлення від $name";
    
    $message = "Ви отримали нове повідомлення зі сторінки підписки.\n\n";
    $message .= "Ім'я: $name\n";
    $message .= "Email: $email\n\n";
    $message .= "Повідомлення:\n$user_message\n";
    
    $headers = "From: webmaster@".$_SERVER['SERVER_NAME']."\r\n";
    $headers .= "Reply-To: $email\r\n";
    $headers .= "Content-Type: text/plain; charset=UTF-8\r\n";

    if (mail($to, $subject, $message, $headers)) {
        echo json_encode(['success' => true]);
    } else {
        echo json_encode(['success' => false, 'error' => 'Поштовий сервер не зміг відправити листа.']);
    }
} else {
    echo json_encode(['success' => false, 'error' => 'Порожні дані.']);
}
?>