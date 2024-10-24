<?php
// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

try {
   $pdo = new PDO("mysql:host=$host;dbname=$dbname", $username, $password);
   $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
   die("Ошибка подключения: " . $e->getMessage());
}

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
   $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
   $scoreToAdd = isset($_GET['score']) ? (int)$_GET['score'] : null;

   error_log("User ID: $userId, Score to Add: $scoreToAdd"); // Отладка

   if ($userId !== null && $scoreToAdd !== null) {
       function addScoreToUser($userId, $scoreToAdd) {
           global $pdo;

           try {
               $stmt = $pdo->prepare("SELECT score FROM users WHERE id = :userId");
               $stmt->execute(['userId' => $userId]);
               $user = $stmt->fetch(PDO::FETCH_ASSOC);

               if ($user) {
                  // Если пользователь существует, обновляем очки
                  $updateStmt = $pdo->prepare("UPDATE users SET score = :newScore WHERE id = :userId");
                  $updateStmt->execute(['newScore' => $scoreToAdd, 'userId' => $userId]); // Устанавливаем новое значение
                  echo "Пользователю с ID {$userId} установлено {$scoreToAdd} очков.";
              } else {
                  // Если пользователя нет, создаем нового с указанным количеством очков
                  $createStmt = $pdo->prepare("INSERT INTO users (id, score) VALUES (:userId, :initialScore)");
                  $createStmt->execute(['userId' => $userId, 'initialScore' => $scoreToAdd]); // Устанавливаем начальное значение
                  echo "Пользователь с ID {$userId} не найден. Создан новый пользователь с {$scoreToAdd} очками.";
              }
              
           } catch (Exception $e) {
               error_log("Ошибка при добавлении очков: " . $e->getMessage());
               echo "Произошла ошибка при добавлении очков.";
           }
       }

       addScoreToUser($userId, $scoreToAdd);
   } else {
       echo "Неправильные данные: убедитесь, что ID пользователя и количество очков переданы корректно.";
   }
} else {
   echo "Используйте GET-запрос для отправки данных.";
}
?>
