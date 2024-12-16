<?php
session_start(); // Запускаем сессию

require 'databaseconnect.php'; // Подключение к базе данных

$userId = $_SESSION['user_id'];

// Получаем список заказов пользователя
$sqlOrders = "SELECT id, date FROM orders WHERE user_id = '$userId';";
$orders = sqlrequest($sqlOrders);

// Для каждого заказа получаем детали (игры и их количество)
foreach ($orders as &$order) {
    $orderId = $order['id'];

    // Получаем игры в заказе
    $sqlOrderItems = "SELECT oi.quantity, g.name, g.price 
                      FROM order_items oi 
                      JOIN games g ON oi.game_id = g.id 
                      WHERE oi.order_id = '$orderId';";
    $orderItems = sqlrequest($sqlOrderItems);

    // Вычисляем общую стоимость заказа
    $totalCost = 0;
    foreach ($orderItems as $item) {
        $totalCost += $item['price'] * $item['quantity'];
    }

    // Добавляем детали заказа в массив
    $order['items'] = $orderItems;
    $order['total_cost'] = $totalCost;
}

header('Content-Type: application/json');
echo json_encode($orders);
?>