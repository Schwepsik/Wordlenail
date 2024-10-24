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

// Получаем ID пользователя из запроса
$userId = isset($_GET['userId']) ? intval($_GET['userId']) : 0;

// Проверяем, что ID пользователя больше 0
if ($userId > 0) {
    // Запрос для получения времени последнего получения награды и номера последнего слота
    $sql = "SELECT time, slot FROM users WHERE id = ?";
    $stmt = $conn->prepare($sql);
    
    if ($stmt === false) {
        die(json_encode(['status' => 'error', 'message' => 'Ошибка подготовки запроса: ' . $conn->error]));
    }

    $stmt->bind_param("i", $userId);
    
    if ($stmt->execute()) {
        $result = $stmt->get_result();
        if ($result->num_rows > 0) {
            $row = $result->fetch_assoc();
            echo json_encode(['status' => 'success', 'time' => $row['time'], 'slot' => $row['slot']]);
        } else {
            echo json_encode(['status' => 'error', 'message' => 'Пользователь не найден.']);
        }
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Ошибка при выполнении запроса: ' . $stmt->error]);
    }

    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'message' => 'Неверный ID пользователя.']);
}

// Закрываем соединение
$conn->close();
?>
