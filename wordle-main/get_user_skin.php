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

    if ($userId !== null) {
        getUserSkin($userId);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неправильные данные: убедитесь, что ID пользователя передан корректно.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Используйте GET-запрос для отправки данных.']);
}

function getUserSkin($userId) {
    global $pdo;

    try {
        // Изменяем запрос, чтобы получить и скин, и статусы купленных скинов
        $stmt = $pdo->prepare("SELECT skin, skin1, skin2, skin3, skin4, skin5, skin6 FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            echo json_encode([
                'status' => 'success',
                'skin' => $user['skin'],
                'skin1' => $user['skin1'],
                'skin2' => $user['skin2'],
                'skin3' => $user['skin3'],
                'skin4' => $user['skin4'],
                'skin5' => $user['skin5'],
                'skin6' => $user['skin6']
            ]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Произошла ошибка при получении скина: " . $e->getMessage()]);
    }
}
?>
