<?php
// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("SET NAMES 'utf8mb4'");
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'message' => "Ошибка подключения: " . $e->getMessage()]);
    exit; // Завершаем выполнение скрипта
}

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $skinId = isset($_GET['skinId']) ? $_GET['skinId'] : null;

    if ($userId !== null && $skinId !== null) {
        addSkinToUser($userId, $skinId);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неправильные данные: убедитесь, что ID пользователя и ID скина переданы корректно.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Используйте GET-запрос для отправки данных.']);
}

function addSkinToUser($userId, $skinId) {
    global $pdo;

    try {
        // Получаем текущую валюту пользователя
        $stmt = $pdo->prepare("SELECT money, skin FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Проверяем, существует ли скин
            $skinPrice = getSkinPrice($skinId);
            if ($skinPrice === null) {
                echo json_encode(['status' => 'error', 'message' => 'Скин не найден.']);
                return;
            }

            $newMoney = $user['money'] - $skinPrice;

            if ($newMoney < 0) {
                echo json_encode(['status' => 'error', 'message' => 'Недостаточно средств для покупки.']);
                return;
            }

            // Обновляем информацию о купленных скинах
            $skinColumn = "skin" . substr($skinId, -1); // Получаем имя столбца, например, skin1, skin2 и т.д.

            // Проверяем, не куплен ли скин ранее
            $stmt = $pdo->prepare("SELECT $skinColumn FROM users WHERE id = :userId");
            $stmt->execute(['userId' => $userId]);
            $skinOwned = $stmt->fetchColumn();

            if ($skinOwned) {
                echo json_encode(['status' => 'error', 'message' => 'Скин уже куплен.']);
                return;
            }

            // Извлекаем номер скина из skinId
            $skinNumber = substr($skinId, -1); // Получаем номер скина (1, 2, 3 и т.д.)

            // Обновляем информацию о пользователе
            $stmt = $pdo->prepare("UPDATE users SET 
                $skinColumn = 1, 
                money = :newMoney,
                skin = :skinNumber 
                WHERE id = :userId");

            $stmt->execute(['newMoney' => $newMoney, 'userId' => $userId, 'skinNumber' => $skinNumber]);

            echo json_encode(['status' => 'success', 'message' => "Скин {$skinId} добавлен пользователю с ID {$userId}."]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Произошла ошибка при добавлении скина: " . $e->getMessage()]);
    }
}

function getSkinPrice($skinId) {
    $prices = [
        'skin1' => 1500,
        'skin2' => 2000,
        'skin3' => 2500,
        'skin4' => 3000,
        'skin5' => 3500,
        'skin6' => 4000,
    ];
    return $prices[$skinId] ?? null; // Возвращаем null, если скин не найден
}
?>
