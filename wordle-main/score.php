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
    die(json_encode(['error' => "Ошибка подключения: " . $e->getMessage()]));
}

// Устанавливаем заголовок для ответа в формате JSON с указанием кодировки
header('Content-Type: application/json; charset=utf-8');

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Получаем данные из GET-запроса
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;

    if ($userId !== null) {
        getOrCreateUserScore($userId);
    } else {
        // Если ID пользователя не передан или неверный
        echo json_encode([
            'status' => 'error',
            'message' => 'Неправильные данные: убедитесь, что ID пользователя передано корректно.'
        ], JSON_UNESCAPED_UNICODE);
    }
} else {
    // Если запрос выполнен не через GET
    echo json_encode([
        'status' => 'error',
        'message' => 'Используйте GET-запрос для отправки данных.'
    ], JSON_UNESCAPED_UNICODE);
}
function getOrCreateUserScore($userId) {
    global $pdo;

    // Запрашиваем очки пользователя из базы данных
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :userId");
    $stmt->execute(['userId' => $userId]);
    $user = $stmt->fetch(PDO::FETCH_ASSOC);

    if ($user) {
        // Если пользователь существует, возвращаем его данные
        echo json_encode([
            'status' => 'success',
            'user_id' => $userId,
            'score' => $user['score'],
            'money' => $user['money'],
            'name' => $user['name'] // Здесь можно оставить как есть
        ], JSON_UNESCAPED_UNICODE);
    }else {
        // Создаем нового пользователя с указанным именем
        $name = isset($_GET['name']) ? $_GET['name'] : 'default_name'; // Получаем имя пользователя
        $createStmt = $pdo->prepare("INSERT INTO users (id, score, name) VALUES (:userId, :initialScore, :name)");
        $createStmt->execute(['userId' => $userId, 'initialScore' => 0, 'name' => $name]);

        // Возвращаем информацию о новом пользователе
        echo json_encode([
            'status' => 'success',
            'message' => "Пользователь с ID {$userId} не найден. Создан новый пользователь.",
            'user_id' => $userId,
            'score' => 0,
            'name' => $name // Возвращаем имя нового пользователя
        ], JSON_UNESCAPED_UNICODE);
    }
}

?>
