<?php
session_start(); // Запускаем сессию

require 'databaseconnect.php'; // Подключение к базе данных

if (isset($_GET['orderId'])) {
    $orderId = $_GET['orderId'];

    // Получаем детали заказа
    $sqlOrderDetails = "SELECT o.id, o.date, o.user_id, oi.quantity, g.name, g.price 
                        FROM orders o
                        JOIN order_items oi ON o.id = oi.order_id
                        JOIN games g ON oi.game_id = g.id
                        WHERE o.id = '$orderId';";
    $orderDetails = sqlrequest($sqlOrderDetails);

    // Вычисляем общую стоимость заказа
    $totalCost = 0;
    foreach ($orderDetails as $item) {
        $totalCost += $item['price'] * $item['quantity'];
    }

    // Формируем ответ
    $response = [
        'id' => $orderId,
        'date' => $orderDetails[0]['date'],
        'user_id' => $orderDetails[0]['user_id'],
        'total_cost' => $totalCost,
        'items' => $orderDetails
    ];

    header('Content-Type: application/json');
    echo json_encode($response);
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Order ID is missing']);
}
?>