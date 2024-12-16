<?php
session_start(); // Запускаем сессию

// Проверяем, авторизован ли пользователь
$userUsername = isset($_SESSION['user_username']) ? $_SESSION['user_username'] : null;

// Подключаем файл с функцией для работы с базой данных
include 'php/databaseconnect.php';

// Получение данных из таблицы games
$games = sqlrequest("SELECT * FROM games");
?>

<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Стартовая страница</title>
    <link rel="stylesheet" href="style.css"> <!-- Подключение стилей -->
    <link href="https://fonts.googleapis.com/css2?family=Roboto:wght@400;700&display=swap" rel="stylesheet">
    <script src="https://cdnjs.cloudflare.com/ajax/libs/inputmask/5.0.6/inputmask.min.js"></script>
</head>
<body>

    <div class="container">

    <div class="navbar">
    <div class="nav-links">
        <a href="#menu">Меню</a>
        <a href="#zakaz">Мои заказы</a>
        <a onclick="openCartPopup('cartPopup')">Корзина</a>
    </div>
    <p id="statusMessage">Вы: <?= $userUsername ? htmlspecialchars($userUsername) : 'не авторизовались' ?></p>
    <div class="auth-buttons">
        <button id="authButton" class="auth-button" onclick="openPopup('authPopup')"><?= $userUsername ? 'Выйти' : 'Авторизация' ?></button>
        <?php if (!$userUsername): ?>
            <button id="registerButton" class="auth-button" onclick="openRegisterPopup()">Регистрация</button>
        <?php endif; ?>
    </div>
</div>

        <!-- Попап для авторизации -->
        <div id="authPopup" class="popup-auth popup" style="display:none;">
            <div class="popup-content auth-popup-content">
                <span class="close" onclick="closePopup('authPopup')">&times;</span>
                <h2>Авторизация</h2>
                <form id="authForm">
                    <div class="form-group">
                        <label for="username">Логин:</label>
                        <input type="text" name="username" id="username" required>
                    </div>
                    <div class="form-group">
                        <label for="password">Пароль:</label>
                        <input type="password" name="password" id="password" required>
                    </div>
                    <button type="submit" class="auth-submit-button">Войти</button>
                </form>
                <div id="responseMessage"></div> <!-- Для отображения сообщений -->
            </div>
        </div>

<!-- Попап для авторизации -->
<div id="authPopup" class="popup-auth popup" style="display:none;">
    <div class="popup-content auth-popup-content">
        <span class="close" onclick="closePopup('authPopup')">&times;</span>
        <h2>Авторизация</h2>
        <form id="authForm">
            <div class="form-group">
                <label for="username">Логин:</label>
                <input type="text" name="username" id="username" placeholder="Login" required>
            </div>
            <div class="form-group">
                <label for="password">Пароль:</label>
                <input type="password" name="password" id="password" placeholder="*********" required>
            </div>
            <button type="submit" class="auth-submit-button">Войти</button>
        </form>
        <div id="responseMessage"></div> <!-- Для отображения сообщений -->
    </div>
</div>

    <!-- Попап для регистрации -->
    <div id="registerPopup" class="popup-auth popup" style="display:none;">
        <div class="popup-content auth-popup-content">
            <span class="close" onclick="closePopup('registerPopup')">&times;</span>
            <h2>Регистрация</h2>
            <form id="registerForm">
                <div class="form-group">
                    <label for="regUsername">Логин:</label>
                    <input type="text" name="username" id="regUsername" placeholder="Login" required maxlength="20">
                </div>
                <div class="form-group">
                    <label for="regPassword">Пароль:</label>
                    <input type="password" name="password" id="regPassword" placeholder="*********" required>
                </div>
                <div class="form-group">
                    <label for="regEmail">Email:</label>
                    <input type="email" name="email" id="regEmail" placeholder="Email@mail.ru" required>
                </div>
                <button type="submit" class="auth-submit-button">Зарегистрироваться</button>
            </form>
            <div id="registerResponseMessage"></div> <!-- Для отображения сообщений -->
        </div>
    </div>
        <!-- Попап для корзины -->
        <div id="cartPopup" class="popup cart-popup" style="display:none;">
            <div class="popup-content auth-popup-content">
                <span class="close" onclick="closePopup('cartPopup')">&times;</span>
                <h2>Корзина</h2>
                <div id="cartItems">
                    <!-- Здесь будут отображаться товары в корзине -->
                    <p>Ваша корзина пуста.</p> <!-- Сообщение по умолчанию -->
                </div>
                <button id="checkoutButton" class="auth-submit-button" onclick="placinganorder()">Оформить заказ</button>
            </div>
        </div>

            <!-- Модальное окно -->
    <div id="orderDetailsModal" class="modal" style="display: none;">
        <div class="modal-content">
            <span class="close" onclick="closeModal()">&times;</span>
            <h2 id="modalOrderTitle">Детали заказа</h2>
            <div id="modalOrderDetails">
                <!-- Здесь будут отображаться детали заказа -->
            </div>
        </div>
    </div>

        <!-- Контейнер 2 : Секция "О нас" -->
        <div class="about-section" id="about">
            <h1>Сайт по продаже видеоигр!</h1>
        </div>

        <!-- Контейнер 3 : Меню с блоками -->
        <div class="menu-block" id="menu">
            <?php if ($games): ?>
                <?php foreach ($games as $game): ?>
                    <!-- Блок меню для каждой игры -->
                    <div class="block">
                       <img src="./img/gameImg/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['name']) ?>" onclick="openPopup('popup<?= $game['id'] ?>')" style="cursor: pointer;">
                        <h3><?= htmlspecialchars($game['name']) ?></h3> <!-- Добавлено название игры -->
                        <p><?= htmlspecialchars($game['Description_sh']) ?></p>
                        <p class="cost-text"><?= htmlspecialchars($game['price']) ?>₽</p>
                        <button class="menu-button" onclick="openPopup('popup<?= $game['id'] ?>')">Подробнее</button>
                    </div>

                    <!-- Попапы для подробного описания -->
                    <div id="popup<?= $game['id'] ?>" class="popup">
                        <div class="popup-content">
                            <span class="close" onclick="closePopup('popup<?= $game['id'] ?>')">&times;</span>
                            <table class="popup-table">
                                <tr>
                                    <td class="popup-image-container">
                                    <img src="./img/gameImg/<?= htmlspecialchars($game['image']) ?>" alt="<?= htmlspecialchars($game['name']) ?>" class="popup-image"> <!-- Картинка игры -->
                                    </td>
                                    <td class="popup-text-container">
                                        <h3 style="display: inline;" class="selected-text">ВЫБРАНА:</h3>
                                        <h2 style="display: inline;"><?= htmlspecialchars($game['name']) ?></h2>
                                        <p><?= htmlspecialchars($game['Description_fu']) ?></p>
                                        <p class="cost-text"><?= htmlspecialchars($game['price']) ?>₽</p>
                                        <div class="button-container">
                                            <button class="add-to-cart" id="<?= htmlspecialchars($game['id']) ?>" data-price="<?= htmlspecialchars($game['price']) ?>">Добавить в корзину</button>
                                        </div>
                                    </td>
                                </tr>
                            </table>
                        </div>
                    </div>

                <?php endforeach; ?>
            <?php else: ?>
                <p>Нет доступных игр.</p>
            <?php endif; ?>
        </div>

    <!-- Контейнер 4 : заказы -->
    <div class="container" id="zakaz">
        <div class="order-container">
            <button onclick="updateOrders()">Обновить</button>
            <div id="ord">

            </div>
        </div>

    <!-- Контейнер 5 : Подвал -->
    <footer id="footer-section">
        <h3 class="contact-title">Контактная информация</h3> 
        <div class="contact-info"> 
            <p>Email: GameShop@mail.ru</p>
            <p>Телефон: +7 (123) 456-78-90</p>
            <p>Социальные сети: 
                <a href="#">Facebook</a>, 
                <a href="#">Telegram</a>, 
                <a href="#">VK</a>
            </p>
        </div>
    </footer>

    </div>

    <!-- Подключение скрипта -->
    <script src="script.js"></script>

</body>
</html>