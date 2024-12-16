<?php
session_start();

require 'databaseconnect.php'; // Подключение к базе данных

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $userUsername = isset($_SESSION['user_username']) ? $_SESSION['user_username'] : null;

    if (!is_null($userUsername) && isset($_POST['cart'])) {
        $cartData = json_decode($_POST['cart'], true);
        $userId = $_SESSION['user_id'];

        // Получаем текущую дату и время
        $currentDate = date('Y-m-d H:i:s');

        // Создаем новый заказ
        $res = sqlrequest("INSERT INTO `orders` (`user_id`, `date`) VALUES ('$userId', '$currentDate'); SELECT LAST_INSERT_ID() AS id;");
        
        $orderId = $res[0]['id'];
        
        $sqlValues = [];
        
        foreach ($cartData as $item) {
            $gameId = $item['productId'];
            $quantity = $item['quantity'];
            // Prepare each value set for insertion
            $sqlValues[] = "($orderId, $gameId, $quantity)";
        }

        if (!empty($sqlValues)) {
            $sql = "INSERT INTO order_items (`order_id`, `game_id`, `quantity`) VALUES " . implode(',', $sqlValues) . ";";

            sqlrequest($sql);
        }

        echo json_encode([
            'isAuthenticated' => true,
            'orderSuccess' => true 
        ]);
    } else {
        echo json_encode(['isAuthenticated' => false]);
    }
} else {
    http_response_code(400);
    echo json_encode(['error' => 'Invalid request']);
}
?>