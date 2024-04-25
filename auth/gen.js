var xhr = new XMLHttpRequest();

function checkAUTH(callback) {
    if (sessionStorage.getItem('auth') != 1) {
        var xhr = new XMLHttpRequest();
        xhr.open('GET', '/auth/check_auth.php', true);
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
    // генерирует кнопки вохода\регистрации
    var divUpper = document.getElementById('upper')
    if (response == 100 || response == 0) {
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
    } else if (response == 1) {
        var registrationForm = document.getElementById("registrationForm");
        if (registrationForm) {
            registrationForm.remove();
        }

        var loginForm = document.getElementById("loginForm");
        if (loginForm) {
            loginForm.remove();
        }
    }
});
