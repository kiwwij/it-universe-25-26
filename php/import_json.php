<?php
header("Content-Type: text/html; charset=utf-8");
try {
    $db = new PDO('sqlite:osbb.db');
    $db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    $json = '[
  { "id": 1, "name": "Іваненко Іван Петрович", "apartment": 1, "entrance": 1, "floor": 1, "area": 52, "currentBalance": 1200, "paymentDue": 800, "debt": 0 },
  { "id": 2, "name": "Петренко Петро Олексійович", "apartment": 2, "entrance": 1, "floor": 1, "area": 67, "currentBalance": -3500, "paymentDue": 1500, "debt": 3500 },
  { "id": 3, "name": "Сидоренко Олег Васильович", "apartment": 3, "entrance": 1, "floor": 1, "area": 45, "currentBalance": 4500, "paymentDue": 1000, "debt": 0 },
  { "id": 4, "name": "Коваленко Марія Іванівна", "apartment": 4, "entrance": 1, "floor": 2, "area": 70, "currentBalance": -2750, "paymentDue": 1200, "debt": 2750 },
  { "id": 5, "name": "Гончар Андрій Сергійович", "apartment": 5, "entrance": 1, "floor": 2, "area": 60, "currentBalance": 800, "paymentDue": 700, "debt": 0 },
  { "id": 6, "name": "Мельник Оксана Петрівна", "apartment": 6, "entrance": 1, "floor": 2, "area": 72, "currentBalance": 3000, "paymentDue": 900, "debt": 0 },
  { "id": 7, "name": "Шевченко Павло Михайлович", "apartment": 7, "entrance": 1, "floor": 3, "area": 50, "currentBalance": -1800, "paymentDue": 600, "debt": 1800 },
  { "id": 8, "name": "Бондар Ірина Володимирівна", "apartment": 8, "entrance": 1, "floor": 3, "area": 62, "currentBalance": 0, "paymentDue": 750, "debt": 0 },
  { "id": 9, "name": "Лисенко Катерина Ігорівна", "apartment": 9, "entrance": 1, "floor": 3, "area": 75, "currentBalance": 2700, "paymentDue": 800, "debt": 0 },
  { "id": 10, "name": "Мороз Володимир Юрійович", "apartment": 10, "entrance": 1, "floor": 4, "area": 40, "currentBalance": -900, "paymentDue": 500, "debt": 900 },

  { "id": 11, "name": "Олійник Дарина Степанівна", "apartment": 11, "entrance": 1, "floor": 4, "area": 90, "currentBalance": 3500, "paymentDue": 1200, "debt": 0 },
  { "id": 12, "name": "Кравченко Олександр Григорович", "apartment": 12, "entrance": 1, "floor": 4, "area": 58, "currentBalance": -4500, "paymentDue": 1300, "debt": 4500 },
  { "id": 13, "name": "Ткаченко Світлана Борисівна", "apartment": 13, "entrance": 1, "floor": 5, "area": 53, "currentBalance": 1100, "paymentDue": 650, "debt": 0 },
  { "id": 14, "name": "Демченко Роман Анатолійович", "apartment": 14, "entrance": 1, "floor": 5, "area": 95, "currentBalance": -5000, "paymentDue": 1600, "debt": 5000 },
  { "id": 15, "name": "Клименко Юлія Дмитрівна", "apartment": 15, "entrance": 1, "floor": 5, "area": 59, "currentBalance": 4200, "paymentDue": 700, "debt": 0 },
  { "id": 16, "name": "Мартинюк Іван Васильович", "apartment": 16, "entrance": 1, "floor": 6, "area": 66, "currentBalance": 250, "paymentDue": 500, "debt": 0 },
  { "id": 17, "name": "Гаврилюк Олена Олександрівна", "apartment": 17, "entrance": 1, "floor": 6, "area": 73, "currentBalance": 3800, "paymentDue": 900, "debt": 0 },
  { "id": 18, "name": "Кириленко Назар Богданович", "apartment": 18, "entrance": 1, "floor": 6, "area": 48, "currentBalance": -1450, "paymentDue": 550, "debt": 1450 },
  { "id": 19, "name": "Поляк Олег Вікторович", "apartment": 19, "entrance": 1, "floor": 7, "area": 83, "currentBalance": 2100, "paymentDue": 800, "debt": 0 },
  { "id": 20, "name": "—", "apartment": 20, "entrance": 1, "floor": 7, "area": 88, "currentBalance": 0, "paymentDue": 1000, "debt": 0 },

  { "id": 21, "name": "Грицай Наталія Володимирівна", "apartment": 21, "entrance": 2, "floor": 7, "area": 61, "currentBalance": -3200, "paymentDue": 900, "debt": 3200 },
  { "id": 22, "name": "Яценко Артем Олегович", "apartment": 22, "entrance": 2, "floor": 8, "area": 92, "currentBalance": 4000, "paymentDue": 1400, "debt": 0 },
  { "id": 23, "name": "Савченко Ірина Віталіївна", "apartment": 23, "entrance": 2, "floor": 8, "area": 64, "currentBalance": 1750, "paymentDue": 700, "debt": 0 },
  { "id": 24, "name": "Білик Максим Андрійович", "apartment": 24, "entrance": 2, "floor": 8, "area": 49, "currentBalance": -2700, "paymentDue": 600, "debt": 2700 },
  { "id": 25, "name": "Захаренко Олексій Миколайович", "apartment": 25, "entrance": 2, "floor": 9, "area": 87, "currentBalance": 3400, "paymentDue": 1000, "debt": 0 },
  { "id": 26, "name": "—", "apartment": 26, "entrance": 2, "floor": 9, "area": 68, "currentBalance": 0, "paymentDue": 850, "debt": 0 },
  { "id": 27, "name": "Федоренко Ольга Іванівна", "apartment": 27, "entrance": 2, "floor": 9, "area": 76, "currentBalance": 2900, "paymentDue": 750, "debt": 0 },
  { "id": 28, "name": "Костенко Павло Сергійович", "apartment": 28, "entrance": 2, "floor": 1, "area": 59, "currentBalance": -2300, "paymentDue": 650, "debt": 2300 },
  { "id": 29, "name": "Романенко Владислав Ігорович", "apartment": 29, "entrance": 2, "floor": 1, "area": 63, "currentBalance": 2600, "paymentDue": 700, "debt": 0 },
  { "id": 30, "name": "Волошин Дмитро Михайлович", "apartment": 30, "entrance": 2, "floor": 1, "area": 47, "currentBalance": -4000, "paymentDue": 1100, "debt": 4000 },

  { "id": 31, "name": "Литвин Анастасія Сергіївна", "apartment": 31, "entrance": 2, "floor": 2, "area": 71, "currentBalance": 3100, "paymentDue": 800, "debt": 0 },
  { "id": 32, "name": "Кравець Олександра Петрівна", "apartment": 32, "entrance": 2, "floor": 2, "area": 82, "currentBalance": 2200, "paymentDue": 950, "debt": 0 },
  { "id": 33, "name": "Черненко Ігор Володимирович", "apartment": 33, "entrance": 2, "floor": 2, "area": 56, "currentBalance": -1250, "paymentDue": 500, "debt": 1250 },
  { "id": 34, "name": "Тимченко Катерина Володимирівна", "apartment": 34, "entrance": 2, "floor": 3, "area": 89, "currentBalance": 900, "paymentDue": 850, "debt": 0 },
  { "id": 35, "name": "Михайленко Сергій Олександрович", "apartment": 35, "entrance": 2, "floor": 3, "area": 65, "currentBalance": -3100, "paymentDue": 700, "debt": 3100 },
  { "id": 36, "name": "—", "apartment": 36, "entrance": 2, "floor": 3, "area": 86, "currentBalance": 0, "paymentDue": 1000, "debt": 0 },
  { "id": 37, "name": "Яременко Олена Степанівна", "apartment": 37, "entrance": 2, "floor": 4, "area": 74, "currentBalance": 1800, "paymentDue": 650, "debt": 0 },
  { "id": 38, "name": "Кравчук Андрій Володимирович", "apartment": 38, "entrance": 2, "floor": 4, "area": 55, "currentBalance": -600, "paymentDue": 400, "debt": 600 },
  { "id": 39, "name": "Соколенко Наталія Петрівна", "apartment": 39, "entrance": 2, "floor": 4, "area": 78, "currentBalance": 2400, "paymentDue": 900, "debt": 0 },
  { "id": 40, "name": "Мельничук Олексій Сергійович", "apartment": 40, "entrance": 2, "floor": 5, "area": 60, "currentBalance": -1500, "paymentDue": 550, "debt": 1500 },

  { "id": 41, "name": "Шаповал Юлія Михайлівна", "apartment": 41, "entrance": 2, "floor": 5, "area": 68, "currentBalance": 1300, "paymentDue": 600, "debt": 0 },
  { "id": 42, "name": "—", "apartment": 42, "entrance": 2, "floor": 5, "area": 72, "currentBalance": 0, "paymentDue": 800, "debt": 0 },
  { "id": 43, "name": "Павленко Олег Віталійович", "apartment": 43, "entrance": 2, "floor": 6, "area": 84, "currentBalance": 3600, "paymentDue": 1200, "debt": 0 },
  { "id": 44, "name": "Іщенко Марина Ігорівна", "apartment": 44, "entrance": 2, "floor": 6, "area": 66, "currentBalance": 2000, "paymentDue": 700, "debt": 0 },
  { "id": 45, "name": "Гнатенко Владислав Олександрович", "apartment": 45, "entrance": 2, "floor": 6, "area": 50, "currentBalance": -900, "paymentDue": 450, "debt": 900 },
  { "id": 46, "name": "Петрова Катерина Олександрівна", "apartment": 46, "entrance": 2, "floor": 7, "area": 80, "currentBalance": 2700, "paymentDue": 850, "debt": 0 },
  { "id": 47, "name": "—", "apartment": 47, "entrance": 2, "floor": 7, "area": 36, "currentBalance": 0, "paymentDue": 400, "debt": 0 },
  { "id": 48, "name": "Кравець Олександра Петрівна", "apartment": 48, "entrance": 2, "floor": 7, "area": 82, "currentBalance": 2200, "paymentDue": 900, "debt": 0 },
  { "id": 49, "name": "—", "apartment": 49, "entrance": 2, "floor": 8, "area": 69, "currentBalance": 0, "paymentDue": 750, "debt": 0 },
  { "id": 50, "name": "Черненко Ігор Володимирович", "apartment": 50, "entrance": 2, "floor": 8, "area": 56, "currentBalance": -1250, "paymentDue": 500, "debt": 1250 },
  { "id": 51, "name": "—", "apartment": 51, "entrance": 2, "floor": 8, "area": 40, "currentBalance": 0, "paymentDue": 400, "debt": 0 },
  { "id": 52, "name": "Тимченко Катерина Володимирівна", "apartment": 52, "entrance": 2, "floor": 9, "area": 89, "currentBalance": 900, "paymentDue": 950, "debt": 0 },
  { "id": 53, "name": "Михайленко Сергій Олександрович", "apartment": 53, "entrance": 2, "floor": 9, "area": 65, "currentBalance": -3100, "paymentDue": 800, "debt": 3100 },
  { "id": 54, "name": "—", "apartment": 54, "entrance": 2, "floor": 9, "area": 86, "currentBalance": 0, "paymentDue": 1000, "debt": 0 }
]';

    $residents = json_decode($json, true);

    $stmt = $db->prepare("INSERT OR REPLACE INTO residents 
        (id, name, apartment, entrance, floor, area, currentBalance, paymentDue, debt) 
        VALUES (:id, :name, :apartment, :entrance, :floor, :area, :currentBalance, :paymentDue, :debt)");

    foreach ($residents as $r) {
        $stmt->execute([
            ':id' => $r['id'],
            ':name' => $r['name'],
            ':apartment' => $r['apartment'],
            ':entrance' => $r['entrance'],
            ':floor' => $r['floor'],
            ':area' => $r['area'],
            ':currentBalance' => $r['currentBalance'],
            ':paymentDue' => $r['paymentDue'],
            ':debt' => $r['debt']
        ]);
    }

    echo "Дані успішно імпортовані!";
} catch (PDOException $e) {
    echo "Помилка: " . $e->getMessage();
}
?>