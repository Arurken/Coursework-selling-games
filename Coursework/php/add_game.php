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

// Обрабатываем загрузку изображения
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = $_POST['name'] ?? '';
    $price = $_POST['price'] ?? '';
    $description_sh = $_POST['description_sh'] ?? '';
    $description_fu = $_POST['description_fu'] ?? '';
    $image = $_FILES['image'] ?? null;

    // Проверяем, что все поля заполнены
    if (empty($name) || empty($price) || empty($description_sh) || empty($description_fu) || empty($image)) {
        header('Location: ../admin.php?error=empty_fields');
        exit;
    }

    // Проверяем, что файл является изображением
    $imageType = strtolower(pathinfo($image['name'], PATHINFO_EXTENSION));
    $allowedTypes = ['jpg', 'jpeg', 'png', 'gif'];

    if (!in_array($imageType, $allowedTypes)) {
        header('Location: ../admin.php?error=invalid_image');
        exit;
    }

    // Генерируем уникальное имя для изображения
    $imageName = uniqid() . '.' . $imageType;
    $uploadPath = '../img/gameImg/' . $imageName;

    // Перемещаем загруженное изображение в папку
    if (!move_uploaded_file($image['tmp_name'], $uploadPath)) {
        header('Location: ../admin.php?error=upload_failed');
        exit;
    }

    // Добавляем игру в базу данных
    $sqlInsertGame = "INSERT INTO games (name, price, Description_sh, Description_fu, image) 
                      VALUES ('$name', '$price', '$description_sh', '$description_fu', '$imageName')";
    $result = sqlrequest($sqlInsertGame);

    if ($result) {
        // Перенаправляем обратно на admin.php с параметром success=true
        header('Location: ../admin.php?success=true');
        exit;
    } else {
        header('Location: ../admin.php?error=db_error');
        exit;
    }
} else {
    http_response_code(400);
    header('Location: ../admin.php?error=invalid_request');
    exit;
}
?>