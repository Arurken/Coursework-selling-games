<?php
require 'databaseconnect.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = json_decode(file_get_contents("php://input"), true);

    $username = $data['username'] ?? '';
    $email = $data['email'] ?? '';

    // Проверяем, существует ли логин
    $sqlCheckUsername = "SELECT * FROM users WHERE username = '$username'";
    $resultUsername = sqlrequest($sqlCheckUsername);

    // Проверяем, существует ли email
    $sqlCheckEmail = "SELECT * FROM users WHERE email = '$email'";
    $resultEmail = sqlrequest($sqlCheckEmail);

    $response = [
        'usernameExists' => !empty($resultUsername),
        'emailExists' => !empty($resultEmail)
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
}
?>