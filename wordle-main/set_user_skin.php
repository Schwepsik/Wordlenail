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

// Проверка, что данные отправлены через POST
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userId = isset($_POST['id']) ? (int)$_POST['id'] : null;
    $selectedSkinId = isset($_POST['skin']) ? $_POST['skin'] : null;

    if ($userId !== null && $selectedSkinId !== null) {
        setUserSkin($userId, $selectedSkinId);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неправильные данные: убедитесь, что ID пользователя и ID скина переданы корректно.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Используйте POST-запрос для отправки данных.']);
}

function setUserSkin($userId, $selectedSkinId) {
    global $pdo;

    try {
        // Проверяем, существует ли пользователь
        $stmt = $pdo->prepare("SELECT COUNT(*) FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        if ($stmt->fetchColumn() == 0) {
            echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
            return;
        }

        // Обновляем скин пользователя в базе данных
        $stmt = $pdo->prepare("UPDATE users SET skin = :skin WHERE id = :userId");
        $stmt->execute(['skin' => $selectedSkinId, 'userId' => $userId]);

        // Проверяем, было ли обновление успешным
        if ($stmt->rowCount() > 0) {
            echo json_encode(['status' => 'success', 'message' => 'Скин успешно изменен']);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Не удалось изменить скин. Возможно, скин уже установлен.']);
        }
    } catch (Exception $e) {
        echo json_encode(['status' => 'error', 'message' => "Произошла ошибка при изменении скина: " . $e->getMessage()]);
    }
}
?>
