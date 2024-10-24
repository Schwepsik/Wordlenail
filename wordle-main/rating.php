<?php
// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

try {
    $pdo = new PDO("mysql:host=$host;dbname=$dbname;charset=utf8mb4", $username, $password);
    // Устанавливаем режим обработки ошибок PDO
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
    $pdo->exec("set names utf8mb4");
} catch (PDOException $e) {
    
    die(json_encode(['error' => "Ошибка подключения: " . $e->getMessage()]));
}

// Устанавливаем заголовок для ответа в формате JSON
header('Content-Type: application/json');

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    // Функция для получения рейтинга пользователей
    function getUserRanking() {
        global $pdo;

        // Запрашиваем всех пользователей и сортируем по количеству очков в порядке убывания
        $stmt = $pdo->query("SELECT * FROM users ORDER BY score DESC");
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);

        if ($users) {
            // Возвращаем список пользователей в формате JSON
            echo json_encode([
                'status' => 'success',
                'ranking' => $users
            ]);
        } else {
            // Если пользователей нет, возвращаем сообщение об этом
            echo json_encode([
                'status' => 'error',
                'message' => 'Нет пользователей в базе данных.'
            ]);
        }
    }

    // Вызываем функцию для получения рейтинга
    getUserRanking();
} else {
    // Если запрос выполнен не через GET
    echo json_encode([
        'status' => 'error',
        'message' => 'Используйте GET-запрос для отправки данных.'
    ]);
}

?>