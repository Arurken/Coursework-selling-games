let cart = new Map();

// Функция для добавления товара в корзину
function addToCart(button) {
    const itemId = button.id;
    const itemName = button.closest('.popup-text-container').querySelector('h2').innerText;
    const itemPrice = parseFloat(button.getAttribute('data-price')); 

    // Проверяем, есть ли уже товар в корзине
    if (cart.has(itemId)) {
        cart.get(itemId).quantity += 1;
    } else {
        cart.set(itemId, {
            name: itemName,
            price: itemPrice,
            quantity: 1
        });
    }

    // Показываем уведомление
    showNotification(`${itemName} добавлена в корзину!`);

    // Закрываем попап
    const popupId = button.closest('.popup').id;
    closePopup(popupId);
}

// Обработчик событий для кнопок "Добавить в корзину"
document.querySelectorAll('.add-to-cart').forEach(button => {
    button.addEventListener('click', function() {
        addToCart(this);
    });
});

// Функция для отображения чека в корзине
function displayCartReceipt() {
    const cartItemsContainer = document.getElementById('cartItems');
    cartItemsContainer.innerHTML = ''; // Очищаем предыдущие элементы
    let totalAmount = 0;

    // Проверяем, есть ли товары в корзине
    if (cart.size === 0) {
        cartItemsContainer.innerHTML = '<p>Ваша корзина пуста.</p>';
        return;
    }

    // Перебираем каждый товар в корзине
    cart.forEach((item, itemId) => {
        const itemTotal = item.price * item.quantity;
        totalAmount += itemTotal;

        // Создаем новый элемент для каждого товара
        const itemElement = document.createElement('div');
        itemElement.className = 'cart-item';

        // Добавляем название игры
        const itemName = document.createElement('p');
        itemName.textContent = `${item.name}`;
        itemElement.appendChild(itemName);

        // Добавляем кнопки "+" и "-"
        const quantityControls = document.createElement('div');
        quantityControls.className = 'quantity-controls';

        const minusButton = document.createElement('button');
        minusButton.textContent = '-';
        minusButton.onclick = () => updateQuantity(itemId, -1);
        quantityControls.appendChild(minusButton);

        const quantityDisplay = document.createElement('span');
        quantityDisplay.textContent = item.quantity;
        quantityControls.appendChild(quantityDisplay);

        const plusButton = document.createElement('button');
        plusButton.textContent = '+';
        plusButton.onclick = () => updateQuantity(itemId, 1);
        quantityControls.appendChild(plusButton);

        itemElement.appendChild(quantityControls);

        // Добавляем общую стоимость товара
        const itemTotalElement = document.createElement('p');
        itemTotalElement.textContent = `Итого: ${itemTotal}₽`;
        itemElement.appendChild(itemTotalElement);

        cartItemsContainer.appendChild(itemElement);
    });

    // Добавляем итоговую сумму в корзину
    const separator = document.createElement('hr'); // Создаем разделительную линию
    const totalElement = document.createElement('p');
    totalElement.textContent = `Итог: ${totalAmount}₽`; // Добавили ₽ для обозначения валюты

    cartItemsContainer.appendChild(separator);
    cartItemsContainer.appendChild(totalElement);
}

function placinganorder() {
    // Проверка на наличие элементов в корзине
    if (cart.size === 0) {
        alert("Корзина пуста!");
        return;
    }

    // Преобразование содержимого корзины в массив
    let cartArray = Array.from(cart.entries()).map(([key, value]) => {
        return { productId: key, quantity: value.quantity };
    });

    // Создание AJAX-запроса
    var xhr = new XMLHttpRequest();
    xhr.open("POST", "php/process_order.php", true);
    xhr.setRequestHeader("Content-Type", "application/x-www-form-urlencoded");

    // Обработка ответа от сервера
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            if (xhr.status === 200) {
                const response = JSON.parse(xhr.responseText);

                if (response.isAuthenticated) {
                    if (response.orderSuccess) {
                        alert("Заказ успешно оформлен!");
                        updateOrders();
                        cart.clear();
                        const cartItemsContainer = document.getElementById('cartItems');
                        cartItemsContainer.innerHTML = 'Ваша корзина пуста.';
                    } else {
                        alert("Ошибка при оформлении заказа.");
                    }
                } else {
                    alert("Пожалуйста, авторизуйтесь, чтобы оформить заказ.");
                }
            } else {
                alert("Ошибка при обработке запроса.");
            }
        }
    };

    // Формирование данных для отправки
    const data = `cart=${encodeURIComponent(JSON.stringify(cartArray))}`;
    
    // Отправка запроса
    xhr.send(data);

}

// Функция для открытия попапа корзины и отображения чека
function openCartPopup() 
{
    openPopup('cartPopup'); // Открываем попап
    displayCartReceipt(); // Отображаем чек перед открытием попапа
    
}

// Функция для открытия попапа
function openPopup(popupId) {
    document.getElementById(popupId).style.display = 'block';
}

// Функция для закрытия попапа
function closePopup(popupId) {
    document.getElementById(popupId).style.display = 'none';
}

// Закрытие попапа при нажатии вне его содержимого
window.onclick = function(event) {
    const popups = document.querySelectorAll('.popup');
    popups.forEach(popup => {
        if (event.target === popup) {
            popup.style.display = 'none';
        }
    });
};

function updateOrders() {
    fetch('php/phpfetch_orders.php')
        .then(response => {
            if (!response.ok) {
                throw new Error('Сеть не отвечает');
            }
            return response.json();
        })
        .then(data => {
            const ordDiv = document.getElementById('ord');
            ordDiv.innerHTML = ''; // Очищаем содержимое div перед добавлением новых данных

            if (data.length > 0) {
                let ordersHtml = '';

                // Перебираем каждый заказ
                data.forEach(order => {
                    const orderId = order.id;
                    const orderDate = order.date;
                    const totalCost = order.total_cost;

                    // Создаем HTML для заказа
                    ordersHtml += `
                        <div class="order-summary">
                            <h3>Заказ #${orderId} - на сумму ${totalCost}₽</h3>
                            <button onclick="loadOrderDetails(${orderId})">Подробнее</button>
                        </div>
                    `;
                });

                ordDiv.innerHTML = ordersHtml; // Добавляем HTML в div
            } else {
                ordDiv.innerHTML = '<p>Нет заказов.</p>'; // Если заказов нет
            }
        })
        .catch(error => {
            const ordDiv = document.getElementById('ord');
            ordDiv.innerHTML = '<p>Вы не авторизовались</p>';
        });
}

// Функция для загрузки деталей заказа через AJAX
function loadOrderDetails(orderId) {
    fetch(`php/phpfetch_order_details.php?orderId=${orderId}`)
        .then(response => {
            if (!response.ok) {
                throw new Error('Сеть не отвечает');
            }
            return response.json();
        })
        .then(orderDetails => {
            showOrderDetailsInModal(orderDetails); // Отображаем детали в модальном окне
        })
        .catch(error => {
            console.error('Ошибка при загрузке деталей заказа:', error);
        });
}

// Функция для отображения деталей заказа в модальном окне
function showOrderDetailsInModal(order) {
    // Заголовок модального окна
    document.getElementById('modalOrderTitle').innerText = `Детали заказа #${order.id}`;

    // Содержимое модального окна
    let detailsHtml = `
        <p><strong>Дата заказа:</strong> ${order.date}</p>
        <p><strong>ID пользователя:</strong> ${order.user_id}</p>
        <p><strong>Общая стоимость:</strong> ${order.total_cost}₽</p>
        <h4>Купленные игры:</h4>
        <ul>
    `;

    // Перебираем игры в заказе
    order.items.forEach(item => {
        const totalItemCost = item.price * item.quantity;
        detailsHtml += `
            <li>${item.name}, ${item.price}₽ в количестве ${item.quantity} шт. Итого: ${totalItemCost}₽</li>
        `;
    });

    detailsHtml += `</ul>`;

    // Добавляем HTML в модальное окно
    document.getElementById('modalOrderDetails').innerHTML = detailsHtml;

    // Открываем модальное окно
    openModal();
}

// Функция для открытия модального окна
function openModal() {
    document.getElementById('orderDetailsModal').style.display = 'block';
}

// Функция для закрытия модального окна
function closeModal() {
    document.getElementById('orderDetailsModal').style.display = 'none';
}

// Обработка авторизации через AJAX
document.getElementById('authForm').onsubmit = function(event) {
    event.preventDefault(); // Отменяем стандартное поведение формы

    var formData = new FormData(this); // Собираем данные формы

    fetch('php/login.php', { // Отправляем AJAX-запрос
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('responseMessage').innerText = data.message; // Отображаем сообщение
        if (data.status === 'success') {
            closePopup('authPopup'); 
            document.getElementById('authButton').innerText = 'Выйти'; // Меняем текст кнопки на "Выйти"
            document.getElementById('statusMessage').innerText = 'Вы: ' + data.username ; 
            updateOrders();
        }
    })
    .catch(error => console.error('Ошибка:', error));
};

// Валидация пароля
function validatePassword(password) {
    const passwordPattern = /^(?=.*[a-z])(?=.*[A-Z])(?=.*\d)(?=.*[!@#$%^&*])[A-Za-z\d!@#$%^&*]{8,}$/;
    return passwordPattern.test(password);
}

// Валидация email
function validateEmail(email) {
    const emailPattern = /^[a-zA-Z0-9._-]+@[a-zA-Z0-9.-]+\.[a-zA-Z]{2,4}$/;
    return emailPattern.test(email);
}

// Обработка регистрации через AJAX
document.getElementById('registerForm').onsubmit = function(event) {
    event.preventDefault(); // Отменяем стандартное поведение формы

    const username = document.getElementById('regUsername').value;
    const password = document.getElementById('regPassword').value;
    const email = document.getElementById('regEmail').value;

    // Проверка длины логина
    if (username.length > 20) {
        alert("Логин не должен превышать 20 символов.");
        return;
    }

    // Проверка пароля
    if (!validatePassword(password)) {
        alert("Пароль должен содержать не менее 8 символов, включая заглавные и строчные буквы, цифры и специальные символы.");
        return;
    }

    // Проверка email
    if (!validateEmail(email)) {
        alert("Пожалуйста, введите корректный email.");
        return;
    }

    var formData = new FormData(this); // Собираем данные формы

    fetch('php/register.php', { // Отправляем AJAX-запрос
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('registerResponseMessage').innerText = data.message; // Отображаем сообщение
        if (data.status === 'success') {
            closePopup('registerPopup'); 
            openPopup('authPopup'); // Открываем форму авторизации после успешной регистрации
        }
    })
    .catch(error => console.error('Ошибка:', error));
};

// Обработка регистрации через AJAX
document.getElementById('registerForm').onsubmit = function(event) {
    event.preventDefault(); // Отменяем стандартное поведение формы

    const username = document.getElementById('regUsername').value;
    const password = document.getElementById('regPassword').value;
    const email = document.getElementById('regEmail').value;

    // Проверка длины логина
    if (username.length < 6) {
        alert("Логин должен содержать не менее 6 символов.");
        return;
    }

    // Проверка длины логина (максимум 20 символов)
    if (username.length > 20) {
        alert("Логин не должен превышать 20 символов.");
        return;
    }

    // Проверка пароля
    if (!validatePassword(password)) {
        alert("Пароль должен содержать не менее 8 символов, включая заглавные и строчные буквы, цифры и специальные символы.");
        return;
    }

    // Проверка email
    if (!validateEmail(email)) {
        alert("Пожалуйста, введите корректный email.");
        return;
    }

    // Проверка уникальности логина и email
    fetch('php/check_unique.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json'
        },
        body: JSON.stringify({ username: username, email: email })
    })
    .then(response => response.json())
    .then(data => {
        if (data.usernameExists) {
            alert("Логин уже занят.");
            return;
        }
        if (data.emailExists) {
            alert("Email уже занят.");
            return;
        }

        // Если все проверки пройдены, отправляем данные на сервер
        var formData = new FormData(this); // Собираем данные формы

        fetch('php/register.php', { // Отправляем AJAX-запрос
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('registerResponseMessage').innerText = data.message; // Отображаем сообщение
            if (data.status === 'success') {
                closePopup('registerPopup'); 
                openPopup('authPopup'); // Открываем форму авторизации после успешной регистрации
            }
        })
        .catch(error => console.error('Ошибка:', error));
    })
    .catch(error => console.error('Ошибка:', error));
};

// Функция для открытия попапа регистрации
function openRegisterPopup() {
    openPopup('registerPopup');
}

// Обработка нажатия кнопки авторизации/выхода
document.getElementById('authButton').onclick = function() {
    if (this.innerText === 'Выйти') {
        fetch('php/login.php', {
            method: 'POST',
            body: new URLSearchParams({ logout: true }) // Отправляем запрос на выход
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('responseMessage').innerText = data.message; // Отображаем сообщение
            this.innerText = 'Авторизация'; // Меняем текст кнопки
            // Обновляем статус авторизации
            document.getElementById('statusMessage').innerText = 'Вы: не авторизовались'; // Обновляем статус
            updateOrders();
        })
        .catch(error => console.error('Ошибка:', error));
    } else {
        openPopup('authPopup');
    }
};

document.querySelector('.navbar a[onclick*="cartPopup"]').addEventListener('click', openCartPopup);

// Функция для скрытия кнопки "Регистрация" после авторизации
function hideRegistrationButton() {
    const authButton = document.getElementById('authButton');
    const registerButton = document.getElementById('registerButton');

    // Если кнопка "Выйти" отображается, скрываем кнопку "Регистрация"
    if (authButton && authButton.innerText === 'Выйти') {
        if (registerButton) {
            registerButton.style.display = 'none';
        }
    } else {
        // Если пользователь не авторизован, показываем кнопку "Регистрация"
        if (registerButton) {
            registerButton.style.display = 'inline-block';
        }
    }
}

// Вызываем функцию при загрузке страницы
window.onload = function() {
    hideRegistrationButton();
};

// Обработка авторизации через AJAX
document.getElementById('authForm').onsubmit = function(event) {
    event.preventDefault(); // Отменяем стандартное поведение формы

    var formData = new FormData(this); // Собираем данные формы

    fetch('php/login.php', { // Отправляем AJAX-запрос
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        document.getElementById('responseMessage').innerText = data.message; // Отображаем сообщение
        if (data.status === 'success') {
            closePopup('authPopup'); 
            document.getElementById('authButton').innerText = 'Выйти'; // Меняем текст кнопки на "Выйти"
            document.getElementById('statusMessage').innerText = 'Вы: ' + data.username; 
            updateOrders();

            // Скрываем кнопку "Регистрация" после успешной авторизации
            hideRegistrationButton();
        }
    })
    .catch(error => console.error('Ошибка:', error));
};

// Обработка нажатия кнопки авторизации/выхода
document.getElementById('authButton').onclick = function() {
    if (this.innerText === 'Выйти') {
        fetch('php/login.php', {
            method: 'POST',
            body: new URLSearchParams({ logout: true }) // Отправляем запрос на выход
        })
        .then(response => response.json())
        .then(data => {
            document.getElementById('responseMessage').innerText = data.message; // Отображаем сообщение
            this.innerText = 'Авторизация'; // Меняем текст кнопки
            document.getElementById('statusMessage').innerText = 'Вы: не авторизовались'; // Обновляем статус
            updateOrders();

            // Показываем кнопку "Регистрация" после выхода
            hideRegistrationButton();
        })
        .catch(error => console.error('Ошибка:', error));
    } else {
        openPopup('authPopup');
    }
};

function showNotification(message) {
    const notification = document.createElement('div');
    notification.textContent = message;
    notification.style.position = 'fixed';
    notification.style.bottom = '20px';
    notification.style.right = '20px';
    notification.style.backgroundColor = 'green';
    notification.style.color = 'white';
    notification.style.padding = '10px';
    notification.style.borderRadius = '5px';
    notification.style.zIndex = '1002'; // Убедитесь, что уведомление отображается поверх всего
    document.body.appendChild(notification);

    // Удаляем уведомление через 3 секунды
    setTimeout(() => {
        notification.remove();
    }, 3000);
}

function updateQuantity(itemId, change) {
    const item = cart.get(itemId);
    if (item) {
        item.quantity += change;

        if (item.quantity <= 0) {
            // Если количество становится 0, спрашиваем, хотите ли вы удалить игру
            const confirmDelete = confirm(`Вы точно хотите удалить "${item.name}" из корзины?`);
            if (confirmDelete) {
                cart.delete(itemId);
            } else {
                item.quantity = 1; // Возвращаем количество к 1, если пользователь отказался удалять
            }
        }

        // Обновляем отображение корзины
        displayCartReceipt();
    }
}