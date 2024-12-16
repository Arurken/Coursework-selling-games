<?php
session_start();

// Проверяем, авторизован ли администратор
$adminUsername = isset($_SESSION['admin_username']) ? $_SESSION['admin_username'] : null;

// Если администратор не авторизован, показываем форму входа
if (!$adminUsername) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        // Подключаем файл с функцией для работы с базой данных
        include 'php/databaseconnect.php';

        $username = $_POST['username'] ?? '';
        $password = $_POST['password'] ?? '';

        // Экранируем входные данные для безопасности
        $username = htmlspecialchars($username);
        $password = htmlspecialchars($password);

        // Запрос для проверки наличия администратора
        $sqlSelectAdmin = "SELECT * FROM admin WHERE username = '$username'";
        $resultsAdmin = sqlrequest($sqlSelectAdmin); // Выполнение запроса

        if (!empty($resultsAdmin)) {
            // Проверяем пароль с помощью password_verify
            if (password_verify($password, $resultsAdmin[0]['password'])) {
                $_SESSION['admin_username'] = $username; // Сохраняем ник в сессии
                header('Location: admin.php'); // Перенаправляем на страницу администратора
                exit;
            } else {
                $errorMessage = "Неправильный логин или пароль.";
            }
        } else {
            $errorMessage = "Неправильный логин или пароль.";
        }
    }

    // Форма входа для администратора
    ?>
    <!DOCTYPE html>
    <html lang="ru">
    <head>
        <meta charset="UTF-8">
        <title>Вход для администратора</title>
        <style>
            /* Стили для формы администратора */
            .container {
                max-width: 600px;
                margin: 0 auto;
                padding: 20px;
                background-color: #f9f9f9;
                border-radius: 10px;
                box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
            }

            .form-group {
                margin-bottom: 20px;
            }

            .form-group label {
                display: block;
                margin-bottom: 5px;
                font-weight: bold;
            }

            .form-group input {
                width: 100%;
                padding: 10px;
                border: 1px solid #ccc;
                border-radius: 5px;
            }

            .auth-submit-button {
                background-color: #28a745;
                color: white;
                border: none;
                padding: 10px 20px;
                border-radius: 5px;
                cursor: pointer;
            }

            .auth-submit-button:hover {
                background-color: #218838;
            }

            .error-message {
                color: red;
                font-weight: bold;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <h1>Вход для администратора</h1>
            <form method="POST" action="admin.php">
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
            <?php if (isset($errorMessage)): ?>
                <p class="error-message"><?= htmlspecialchars($errorMessage) ?></p>
            <?php endif; ?>
        </div>
    </body>
    </html>
    <?php
    exit; // Останавливаем выполнение скрипта, чтобы не показывать интерфейс администратора
}

// Если администратор авторизован, показываем интерфейс для добавления и удаления игр
?>
<!DOCTYPE html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <title>Панель администратора</title>
    <style>
        /* Стили для формы администратора */
        .container {
            max-width: 600px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f9f9f9;
            border-radius: 10px;
            box-shadow: 0 2px 5px rgba(0, 0, 0, 0.1);
        }

        .form-group {
            margin-bottom: 20px;
        }

        .form-group label {
            display: block;
            margin-bottom: 5px;
            font-weight: bold;
        }

        .form-group input,
        .form-group textarea,
        .form-group select {
            width: 100%;
            padding: 10px;
            border: 1px solid #ccc;
            border-radius: 5px;
        }

        .auth-submit-button {
            background-color: #28a745;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 5px;
            cursor: pointer;
        }

        .auth-submit-button:hover {
            background-color: #218838;
        }

        .success-message {
            color: green;
            font-weight: bold;
        }

        .error-message {
            color: red;
            font-weight: bold;
        }

        .disabled-option {
            color: gray;
            font-style: italic;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Панель администратора</h1>
        <p>Добро пожаловать, <?= htmlspecialchars($adminUsername) ?>!</p>

        <!-- Форма для добавления игры -->
        <form method="POST" action="php/add_game.php" enctype="multipart/form-data">
            <div class="form-group">
                <label for="name">Название игры:</label>
                <input type="text" name="name" id="name" required>
            </div>
            <div class="form-group">
                <label for="price">Цена:</label>
                <input type="number" name="price" id="price" step="0.01" required>
            </div>
            <div class="form-group">
                <label for="description_sh">Краткое описание:</label>
                <textarea name="description_sh" id="description_sh" required></textarea>
            </div>
            <div class="form-group">
                <label for="description_fu">Полное описание:</label>
                <textarea name="description_fu" id="description_fu" required></textarea>
            </div>
            <div class="form-group">
                <label for="image">Изображение:</label>
                <input type="file" name="image" id="image" accept="image/*" required>
            </div>
            <button type="submit" class="auth-submit-button">Добавить игру</button>
        </form>

        <!-- Форма для удаления игры -->
        <h2>Удаление игры</h2>
        <form method="POST" action="php/delete_game.php">
            <div class="form-group">
                <label for="game_id">Выберите игру для удаления:</label>
                <select name="game_id" id="game_id" required>
                    <?php
                    // Подключаем файл с функцией для работы с базой данных
                    include 'php/databaseconnect.php';

                    // Получаем список игр из базы данных
                    $sqlSelectGames = "SELECT id, name FROM games";
                    $games = sqlrequest($sqlSelectGames);

                    if (!empty($games)) {
                        foreach ($games as $game) {
                            // Проверяем, есть ли связанные записи в order_items
                            $sqlCheckRelated = "SELECT COUNT(*) as count FROM order_items WHERE game_id = '{$game['id']}'";
                            $relatedCount = sqlrequest($sqlCheckRelated);

                            if ($relatedCount[0]['count'] > 0) {
                                // Если есть связанные записи, добавляем метку
                                echo "<option value='{$game['id']}' disabled class='disabled-option'>{$game['name']} - Данную игру нельзя удалить!</option>";
                            } else {
                                // Если связанных записей нет, добавляем игру в список
                                echo "<option value='{$game['id']}'>{$game['name']}</option>";
                            }
                        }
                    } else {
                        echo "<option value='' disabled>Нет доступных игр</option>";
                    }
                    ?>
                </select>
            </div>
            <button type="submit" class="auth-submit-button">Удалить игру</button>
        </form>

        <!-- Сообщения об успехе или ошибке -->
        <?php
        // Проверяем, есть ли сообщение об успешном добавлении игры
        if (isset($_GET['success']) && $_GET['success'] === 'true') {
            echo '<p class="success-message">Игра успешно добавлена!</p>';
        }

        // Проверяем, есть ли сообщение об успешном удалении игры
        if (isset($_GET['delete_success']) && $_GET['delete_success'] === 'true') {
            echo '<p class="success-message">Игра успешно удалена!</p>';
        }

        // Проверяем, есть ли сообщение об ошибке
        if (isset($_GET['error'])) {
            $errorMessage = '';
            switch ($_GET['error']) {
                case 'empty_fields':
                    $errorMessage = 'Пожалуйста, выберите игру для удаления.';
                    break;
                case 'delete_failed':
                    $errorMessage = 'Ошибка при удалении игры.';
                    break;
                case 'invalid_request':
                    $errorMessage = 'Неверный запрос.';
                    break;
            }
            if (!empty($errorMessage)) {
                echo '<p class="error-message">' . htmlspecialchars($errorMessage) . '</p>';
            }
        }
        ?>

        <br>
        <a href="admin.php?logout=true">Выйти</a>
    </div>
</body>
</html>
<?php
// Обработка выхода
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: admin.php');
    exit;
}
?>