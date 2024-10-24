<?php
// Подключение к базе данных
$host = '37.140.192.16'; // Имя хоста
$dbname = 'u2845244_default'; // Имя вашей базы данных
$username = 'u2845244_default'; // Логин от базы данных
$password = 'nF0nM6wC1vmW2dH1'; // Пароль от базы данных

$conn = new mysqli($servername, $username, $password, $dbname);

// Проверка соединения
if ($conn->connect_error) {
    die(json_encode(['status' => 'error', 'message' => 'Ошибка подключения к базе данных']));
}

// Получение данных из POST-запроса
$userId = $_POST['id'];
$skinId = $_POST['skin'];

// Здесь вы можете добавить логику для проверки, достаточно ли у пользователя валюты для покупки скина
// Например, вы можете проверить, есть ли у пользователя достаточно средств в базе данных

// Пример запроса на обновление информации о скине пользователя
$sql = "UPDATE users SET skin$skinId = 1 WHERE id = ?";
$stmt = $conn->prepare($sql);
$stmt->bind_param("i", $userId);

if ($stmt->execute()) {
    echo json_encode(['status' => 'success']);
} else {
    echo json_encode(['status' => 'error', 'message' => 'Не удалось купить скин']);
}

$stmt->close();
$conn->close();
?>
