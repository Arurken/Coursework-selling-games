<?php
session_start(); // Запускаем сессию

require 'databaseconnect.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (isset($_POST['logout'])) {
        // Выход из системы
        session_destroy();
        echo json_encode(["status" => "success", "message" => "Вы вышли из системы."]);
        exit;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    // Экранируем входные данные для безопасности
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);

    // Запрос для проверки наличия пользователя
    $sqlSelectUser = "SELECT * FROM users WHERE username = '$username'";
    $resultsUser = sqlrequest($sqlSelectUser); // Выполнение запроса

    if (!empty($resultsUser)) {
        // Проверяем пароль с помощью password_verify
        if (password_verify($password, $resultsUser[0]['password'])) {
            $_SESSION['user_username'] = $username; // Сохраняем ник в сессии
            $_SESSION['user_id'] = $resultsUser[0]['id'];
            echo json_encode(["status" => "success", "message" => "Успешная авторизация!", "username" => $username]);
        } else {
            echo json_encode(["status" => "error", "message" => "Неправильный логин или пароль."]);
        }
    } else {
        echo json_encode(["status" => "error", "message" => "Неправильный логин или пароль."]);
    }
}
?>