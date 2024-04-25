var info; // инфа о переписке
var xhr = new XMLHttpRequest();
var params = new URLSearchParams(window.location.search);
var userId = ((document.cookie.match('(^|; )' + encodeURIComponent('user-id') + '=([^;]+)') || []).pop() || null);
var socket = new WebSocket('ws://wall-cast.ru:8080?userId=' + userId);

// WebSocket отправка на сервер
function sendWebSocketMessage(data) {
    // Проверяем, что соединение WebSocket открыто
    if (socket.readyState === WebSocket.OPEN) {
        // Отправляем сообщение на сервер
        socket.send(JSON.stringify(data));
    } else {
        console.error('WebSocket connection is not open');
    }
}

// WebSocket обработка новых сообщений
socket.onmessage = function(event) {
    // Получаем данные из полученного сообщения
    var data = JSON.parse(event.data);
    if (data.recipient == userId) {
        displayNewMessage(data);
    } else {
        console.error('Fatal Error: ' + data)
    }
};

function displayNewMessage(data) {
    var sender = data.userId;
    var message_id = data.message_id;
    var timestamp = data.timestamp;
    var message = data.message;
    var container = document.getElementById('dialogs');
    var li = document.createElement('li');
    li.innerText = JSON.stringify(data);
    container.appendChild(li);
}

// Отправка сообщений
function send_message(e) {
    var params = new URLSearchParams(window.location.search);
    var idValue = params.get('id');
    var data = JSON.stringify({'operation': 'SENDMESSAGE', 'user_id': idValue, 'message': e});
    xhr.open('POST', `/messages/chats.php`, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            var response = xhr.responseText;
            console.log(response);

            // Отправка всем по WebSocket
            sendWebSocketMessage({ 'senderID': userId, 'recipientUserId': idValue, 'message': e, 'dialog_id': info.dialog_id });
        }
    };
    xhr.send(data);
}

// добавляем обработчик на новые чаты
function attachClickHandlerToLinks() {
    var ul = document.getElementById('dialogs');
    var links = ul.querySelectorAll('li > a');
    links.forEach(function(link) {
        link.addEventListener('click', function(event) {
            event.preventDefault();
            var href = link.getAttribute('href');
            history.pushState(null, null, href);
            genLS();
        });
    });
}

// генерация лс
function genLS() {
    console.log('genLS')
    var params = new URLSearchParams(window.location.search);
    var idValue = params.get('id');
    var container = document.getElementById('earth');
    container.innerHTML = '<ul id="dialogs"></ul>';
    var data = {'operation': 'GETMESSANGES', 'n': 1, 'user_id': idValue};
    xhr.open('POST', `/messages/chats.php`, true);
    xhr.onreadystatechange = function() {
        if (xhr.readyState === XMLHttpRequest.DONE) {
            var response = xhr.responseText;
            if (response != 'none') {
                var div = document.createElement('div');
                div.className = "send-form";
                div.innerHTML = `<input type="text" id="input-message"><button class="send-message" id="button-send"></button>`;
                container.appendChild(div);
                // обрабатываем json ответ, добавляем сообщения
                response = JSON.parse(response);
                info = response.info;
                response = response.messages;
                var keys = Object.keys(response);
                container = document.getElementById('dialogs');
                for (var i = 0; i < keys.length; i++) {
                    var key = keys[i];
                    var value = response[key];
                    
                    var li = document.createElement('li');
                    li.innerHTML = `${key}: ${JSON.stringify(value)}`;
                    
                    container.appendChild(li);
                }
                var page_fixer = document.createElement('div');
                page_fixer.classList.add('page-fixer');
                container.appendChild(page_fixer);
                // send message, поле ввода, отправки сообщений
                var buttonSend = document.getElementById("button-send");
                var inp = document.getElementById("input-message");

                // ивент при нажатии кнопки enter, отправляем сообщение
                inp.addEventListener("keydown", function (e) {
                    if (e.code === "Enter") {
                        send_message(e.target.value);
                        inp.value = "";
                    }
                });

                // button-send, при нажатии отправляем сообщение
                buttonSend.addEventListener("click", function() {
                    send_message(inputMessage.value);
                    inp.value = "";
                });
            }
        }
    };
    xhr.send(data);
}

// callback для проверки входа в аккаунт
function checkAUTH(callback) {
    if (sessionStorage.getItem('auth') != 1) {
        var xhr = new XMLHttpRequest();
        xhr.open('POST', '/auth/check_auth.php', true);
        xhr.onreadystatechange = function() {
            if (xhr.readyState === XMLHttpRequest.DONE) {
                sessionStorage.setItem('auth', xhr.responseText);
                callback(xhr.responseText);
            }
        };
        xhr.send();
    } else {
        callback(sessionStorage.getItem('auth'));
    }
}

checkAUTH(function(response) {
    // генерирует переписки
    if (response == 1) {
        // пользователь уже вошёл в аккаунт
        var registrationForm = document.getElementById("registrationForm");
        if (registrationForm) {
            registrationForm.remove();
        }

        var loginForm = document.getElementById("loginForm");
        if (loginForm) {
            loginForm.remove();
        }
        
        if (params.has('id')) {
            // есть id, генерируем личную переписку
            genLS();
        } else {
            // генерируем все чаты
            // получаем все диалоги
            var data = {'operation': 'GETLASTMESSAGE'};
            xhr.open('POST', '/messages/chats.php', true);
            xhr.onreadystatechange = function() {
                if (xhr.readyState === XMLHttpRequest.DONE) {
                    var response = xhr.responseText;
                    if (response != 'none') {
                        response = JSON.parse(response);
                        var keys = Object.keys(response);
                        var container = document.getElementById('dialogs');
                        // добавляем диалоги из json ответа 
                        for (var i = 0; i < keys.length; i++) {
                            var key = keys[i];
                            var value = response[key];
                            
                            var li = document.createElement('li');
                            li.id = value.info.user_2;
                            li.innerHTML = `<a href="/messages?id=${value.info.user_2}">${key}: ${JSON.stringify(value)}</a>`;
                            
                            container.appendChild(li);
                        }
                        attachClickHandlerToLinks();
                    }
                }
            };
            xhr.send();
            }
        // end
    } else if (response == 100 || response == 0) {
        var divUpper = document.getElementById('upper')
        var profile = document.getElementById('img');

        if (profile) {
            profile.remove();

            // не вошёл в аккаунт
            var button_log = document.createElement("button");
            button_log.classList.add("login-button", "heaven-button-anim-1", "heaven-button");
            button_log.id = "openFormButton-login";
            button_log.innerHTML = `Вход в аккаунт`;
            divUpper.appendChild(button_log);
            
            var button_reg = document.createElement("button");
            button_reg.classList.add("registration-button", "heaven-button-anim-1", "heaven-button");
            button_reg.id = "openFormButton-registration";
            button_reg.innerHTML = `Регистрация`;
            divUpper.appendChild(button_reg);
            
            if (response == 0) {
                // неправильный токен
                console.log('auth token uncorrect')
            }
            var hideFormButton = document.getElementById('hideFormButton');
            var registrationForm = document.getElementById('registrationForm');
            var loginForm = document.getElementById('loginForm');
            var openFormButtonReg = document.getElementById('openFormButton-registration');
            var openFormButtonLog = document.getElementById('openFormButton-login');
            
            // открытие - закрытие формы регистрации и входа
            openFormButtonReg.addEventListener('click', function() {
                registrationForm.classList.remove('hidden');
            });
            
            openFormButtonLog.addEventListener('click', function() {
                loginForm.classList.remove('hidden');
            });
            
            document.querySelectorAll('.hideFormButton').forEach(function(button) {
                button.addEventListener('click', function() {
                    loginForm.classList.add('hidden');
                    registrationForm.classList.add('hidden');
                });
            });
            
            // отправка форм
            // регистрация
            document.getElementById('registrationFormSend').addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/auth/registration.php', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            var response = xhr.responseText;
                            if (response == "login_busy") {
                                // если логин занят
                                console.log('login busy')
                            } else {
                                // если получилось добавить в DB
                                
                                // получаем ответ
                                var values = JSON.parse(response)
                                var auth_token = values.authtoken;
                                var login = values.login;
                                var username = values.username;
                                var userID = values.userid;
                                var currentDate = new Date();
                                var futureDate = new Date();
                                futureDate.setDate(currentDate.getDate() + 30);
                                var expires = futureDate.toUTCString();
                                // добавляем куки
                                document.cookie = `auth-token=${auth_token}; expires=${expires}; path=/`;
                                document.cookie = `login=${login}; expires=${expires}; path=/`;
                                document.cookie = `user-name=${username}; expires=${expires}; path=/`;
                                document.cookie = `user-id=${userID}; expires=${expires}; path=/`;
                                
                                location.reload();
                            }
                        }
                    }
                };
                xhr.send(formData);
            });
            
            // вход
            document.getElementById('loginFormSend').addEventListener('submit', function(event) {
                event.preventDefault();
                var formData = new FormData(this);
                var xhr = new XMLHttpRequest();
                xhr.open('POST', '/auth/login.php', true);
                xhr.setRequestHeader('X-Requested-With', 'XMLHttpRequest');
                xhr.onreadystatechange = function() {
                    if (xhr.readyState === XMLHttpRequest.DONE) {
                        if (xhr.status === 200) {
                            var response = xhr.responseText;
                            if (response == "inc_password") {
                                // если пароль неверный
                            } else if (response == "inc_log") {
                                // если не нашлось логина в DB
                                
                            } else {
                                // если пароль верный
                                
                                // получаем ответ
                                var values = response.split('|')
                                var auth_token = values[0];
                                var login = values[1];
                                var username = values[2];
                                var userID = values[3];
                                var currentDate = new Date();
                                var futureDate = new Date();
                                futureDate.setDate(currentDate.getDate() + 30);
                                var expires = futureDate.toUTCString();
                                // добавляем куки
                                document.cookie = `auth-token=${auth_token}; expires=${expires}; path=/`;
                                document.cookie = `login=${login}; expires=${expires}; path=/`;
                                document.cookie = `user-name=${username}; expires=${expires}; path=/`;
                                document.cookie = `user-id=${userID}; expires=${expires}; path=/`;
                                
                                location.reload();
                            }
                        }
                    }
                };
                xhr.send(formData);
            });
        }
    }
});
