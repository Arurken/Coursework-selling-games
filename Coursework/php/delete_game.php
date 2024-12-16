<?php
session_start();

// Проверяем, авторизован ли администратор
if (!isset($_SESSION['admin_username'])) {
    http_response_code(403);
    echo json_encode(['error' => 'Доступ запрещен']);
    exit;
}

// Подключаем файл с функцией для работы с базой данных
require 'databaseconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $gameId = $_POST['game_id'] ?? '';

    if (empty($gameId)) {
        header('Location: ../admin.php?error=empty_fields');
        exit;
    }

    // Удаляем игру из базы данных
    $sqlDeleteGame = "DELETE FROM games WHERE id = '$gameId'";
    $result = sqlrequest($sqlDeleteGame);

    if ($result) {
        // Перенаправляем обратно на admin.php с параметром success=true
        header('Location: ../admin.php?delete_success=true');
        exit;
    } else {
        header('Location: ../admin.php?error=delete_failed');
        exit;
    }
} else {
    http_response_code(400);
    header('Location: ../admin.php?error=invalid_request');
    exit;
}
?>