<?php
// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

try {
   $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
   // Устанавливаем режим обработки ошибок PDO
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   die(json_encode(['error' => "Ошибка подключения: " . $e->getMessage()]));
}

// Устанавливаем заголовок для ответа в формате JSON
header('Content-Type: application/json');

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   // Получаем данные из GET-запроса
   $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
   
   if ($userId !== null) {
       // Функция для получения очков пользователя или создания нового
       function getOrCreateUserScore($userId) {
           global $pdo;

           // Запрашиваем очки пользователя из базы данных
           $stmt = $pdo->prepare("SELECT * FROM users WHERE id = :userId");
           $stmt->execute(['userId' => $userId]);
           $user = $stmt->fetch(PDO::FETCH_ASSOC);

           if ($user) {
               // Если пользователь найден, возвращаем его очки в формате JSON
               echo json_encode([
                   'status' => 'success',
                   'user_id' => $userId,
                   'score' => $user['score'],
                   'money' => $user['money']
               ]);
           } else {
            $name = isset($_GET["name"]) ? $_GET["name"] : 'default_value';
               // Если пользователь не найден, создаем нового пользователя с 0 очков
               $createStmt = $pdo->prepare("INSERT INTO users (id, score, name) VALUES (:userId, :initialScore, :name)");
               $createStmt->execute(['userId' => $userId, 'initialScore' => 0,'name' => $name]);

               // Возвращаем информацию о новом пользователе
               echo json_encode([
                   'status' => 'success',
                   'message' => "Пользователь с ID {$userId} не найден. Создан новый пользователь.",
                   'user_id' => $userId,
                   'score' => 0
               ]);
           }
       }

       // Вызываем функцию для получения или создания пользователя
       getOrCreateUserScore($userId);
   } else {
       // Если ID пользователя не передан или неверный
       echo json_encode([
           'status' => 'error',
           'message' => 'Неправильные данные: убедитесь, что ID пользователя передано корректно.'
       ]);
   }
} else {
   // Если запрос выполнен не через GET
   echo json_encode([
       'status' => 'error',
       'message' => 'Используйте GET-запрос для отправки данных.'
   ]);
}

?>