<?php
date_default_timezone_set('Europe/Kyiv');
require_once "dompdf/autoload.inc.php";
use Dompdf\Dompdf;
use Dompdf\Options;

if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Отримуємо дані з URL параметрів
    $name = $_GET['name'] ?? 'Гість';
    $email = $_GET['email'] ?? '-';
    $plan = $_GET['plan'] ?? '-';
    $price = $_GET['price'] ?? '0';
    $orderId = $_GET['orderId'] ?? '000000';
    $date = date("d.m.Y H:i");

    $html = "
    <html>
    <head>
        <meta http-equiv='Content-Type' content='text/html; charset=utf-8'/>
        <style>
            body { font-family: 'DejaVu Sans', sans-serif; font-size: 14px; color: #333; }
            .header { text-align: center; border-bottom: 2px solid #28a745; padding-bottom: 10px; }
            .content { margin-top: 20px; }
            .row { display: block; margin-bottom: 10px; border-bottom: 1px solid #eee; }
            .label { font-weight: bold; }
            .footer { margin-top: 50px; text-align: center; font-size: 10px; color: #777; }
        </style>
    </head>
    <body>
        <div class='header'>
            <h2>КВИТАНЦІЯ № $orderId</h2>
            <p>Платформа ОСББ — Електронний чек</p>
        </div>
        <div class='content'>
            <p><span class='label'>Дата:</span> $date</p>
            <p><span class='label'>План:</span> $plan</p>
            <p><span class='label'>Сума:</span> ₴$price</p>
            <hr>
            <p><span class='label'>Платник:</span> $name</p>
            <p><span class='label'>Email:</span> $email</p>
            <p><span class='label'>Статус:</span> ОПЛАЧЕНО</p>
        </div>
        <div class='footer'>Дякуємо за оплату! © 2025 ОСББ Платформа</div>
    </body>
    </html>";

    $options = new Options();
    $options->set('defaultFont', 'DejaVu Sans');
    $dompdf = new Dompdf($options);
    $dompdf->loadHtml($html);
    $dompdf->setPaper('A5', 'portrait');
    $dompdf->render();
    
    $dompdf->stream("receipt_$orderId.pdf", ["Attachment" => 1]);
    exit();
}