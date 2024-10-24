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

// Проверка, что данные отправлены через GET
if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    $userId = isset($_GET['id']) ? (int)$_GET['id'] : null;
    $scoreToAdd = isset($_GET['score']) ? (int)$_GET['score'] : null;
    $name = isset($_GET['name']) ? $_GET['name'] : 'default_name'; // Получаем имя пользователя

    if ($userId !== null && $scoreToAdd !== null) {
        addScoreToUser($userId, $scoreToAdd, $name);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Неправильные данные: убедитесь, что ID пользователя и количество очков переданы корректно.']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Используйте GET-запрос для отправки данных.']);
<<<<<<< Updated upstream
=======
}

function addScoreToUser($userId, $scoreToAdd, $name) {
    global $pdo;

    try {
        error_log("Попытка добавить очки: userId = $userId, scoreToAdd = $scoreToAdd, name = $name"); // Отладка

        // Запрашиваем текущие очки и деньги пользователя
        $stmt = $pdo->prepare("SELECT score, money FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Если пользователь существует, обновляем очки и деньги
            $newMoney = $user['money'] + 10; // Увеличиваем деньги на 10 за каждое угаданное слово
            $updateStmt = $pdo->prepare("UPDATE users SET score = score + :newScore, money = :newMoney WHERE id = :userId");
            $updateStmt->execute(['newScore' => $scoreToAdd, 'newMoney' => $newMoney, 'userId' => $userId]);
            
            // Возвращаем обновленные данные
            echo json_encode(['status' => 'success', 'message' => "Пользователю с ID {$userId} добавлено {$scoreToAdd} очков и обновлены деньги.", 'money' => $newMoney]);
        } else {
            // Если пользователя нет, создаем нового с указанным количеством очков и деньгами
            $createStmt = $pdo->prepare("INSERT INTO users (id, score, money, name) VALUES (:userId, :initialScore, :initialMoney, :name)");
            $createStmt->execute(['userId' => $userId, 'initialScore' => $scoreToAdd, 'initialMoney' => 0, 'name' => $name]);
            echo json_encode(['status' => 'success', 'message' => "Создан новый пользователь с ID {$userId} и именем {$name}.", 'money' => 0]);
        }
    } catch (Exception $e) {
        error_log("Ошибка при добавлении очков: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => "Произошла ошибка при добавлении очков: " . $e->getMessage()]);
    }
>>>>>>> Stashed changes
}

function addScoreToUser($userId, $scoreToAdd, $name) {
    global $pdo;

    try {
        // Запрашиваем текущие очки и деньги пользователя
        $stmt = $pdo->prepare("SELECT score, money FROM users WHERE id = :userId");
        $stmt->execute(['userId' => $userId]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);

        if ($user) {
            // Если пользователь существует, обновляем очки и деньги
            $newMoney = $user['money'] + 10; // Увеличиваем деньги на 10 за каждое угаданное слово
            $updateStmt = $pdo->prepare("UPDATE users SET score = score + :newScore, money = :newMoney WHERE id = :userId");
            $updateStmt->execute(['newScore' => $scoreToAdd, 'newMoney' => $newMoney, 'userId' => $userId]);
            
            // Возвращаем обновленные данные
            echo json_encode(['status' => 'success', 'message' => "Пользователю с ID {$userId} добавлено {$scoreToAdd} очков и обновлены деньги.", 'money' => $newMoney]);
        } else {
            // Если пользователя нет, создаем нового с указанным количеством очков и деньгами
            $createStmt = $pdo->prepare("INSERT INTO users (id, score, money, name) VALUES (:userId, :initialScore, :initialMoney, :name)");
            $createStmt->execute(['userId' => $userId, 'initialScore' => $scoreToAdd, 'initialMoney' => 0, 'name' => $name]);
            echo json_encode(['status' => 'success', 'message' => "Создан новый пользователь с ID {$userId} и именем {$name}.", 'money' => 0]);
        }
    } catch (Exception $e) {
        error_log("Ошибка при добавлении очков: " . $e->getMessage());
        echo json_encode(['status' => 'error', 'message' => "Произошла ошибка при добавлении очков: " . $e->getMessage()]);
    }
}

?>
