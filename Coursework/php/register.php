<?php
session_start();
require 'databaseconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    $email = $_POST['email'] ?? '';

    // Экранируем входные данные для безопасности
    $username = htmlspecialchars($username);
    $password = htmlspecialchars($password);
    $email = htmlspecialchars($email);

    // Хешируем пароль
    $hashedPassword = password_hash($password, PASSWORD_DEFAULT);

    // Запрос для вставки нового пользователя
    $sqlInsertUser = "INSERT INTO users (username, password, email) VALUES ('$username', '$hashedPassword', '$email')";
    $result = sqlrequest($sqlInsertUser);

    if ($result) {
        echo json_encode(["status" => "success", "message" => "Регистрация прошла успешно!"]);
    } else {
        echo json_encode(["status" => "error", "message" => "Ошибка при регистрации."]);
    }
}
?>