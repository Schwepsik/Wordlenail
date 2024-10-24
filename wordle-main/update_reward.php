<?php
// Включаем отображение ошибок для отладки
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

// Создаем соединение
$conn = new mysqli($host, $username, $password, $dbname);

// Проверяем соединение
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка подключения: ' . $conn->connect_error]));
}

// Получаем параметры из запроса
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0; // ID пользователя
$money = isset($_GET['money']) ? intval($_GET['money']) : 0; // Измененное значение валюты
$slot = isset($_GET['slot']) ? intval($_GET['slot']) : 0; // Номер слота
$time = time(); // Текущее время

// Проверяем, что ID пользователя больше 0
if ($userId > 0) {
    // Обновляем значения в базе данных
    $sql = "UPDATE users SET money = money + ?, time = ?, slot = ? WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error]));
    }

    $stmt->bind_param("iiii", $money, $time, $slot, $userId);

    if ($stmt->execute()) {
        // Получаем обновленное время и слот
        $sql = "SELECT time, slot FROM users WHERE id = ?";
        $stmt = $conn->prepare($sql);
        $stmt->bind_param("i", $userId);
        $stmt->execute();
        $stmt->bind_result($updatedTime, $updatedSlot);
        $stmt->fetch();

        echo json_encode([
            'status' => 'success',
            'message' => 'Награда успешно обновлена.',
            'rewardTime' => $updatedTime, // Возвращаем обновленное время
            'slot' => $updatedSlot // Возвращаем обновленный слот
        ]);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при обновлении награды: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный ID пользователя.']);
}

// Закрываем соединение
$conn->close();
?>
