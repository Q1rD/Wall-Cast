<!doctype html>
<html lang="ru">
<head>
    <meta charset="UTF-8">
    <link rel="icon" href="/favicon.ico">
    <link rel="stylesheet" href="/style.css">
    <title>WallCast</title>
</head>
<body>
    <div class="heaven" id="upper">
        <a href="/" class="link">
            <svg width="45" height="45" class="logo" role="img" fill="none">
                  <g>
                    <rect id="svg_27" height="45" width="45" y="0.09259" x="0.18519" stroke-width="0" stroke="null" fill="#000000"/>
                    <rect stroke="null" rx="0.5" id="svg_13" height="29.81987" width="3.62963" y="7.59006" x="20.74328" stroke-width="0" fill="#f2f2f2"/>
                    <rect stroke="null" rx="0.5" id="svg_14" height="10" width="3.35185" y="17.5" x="15.09259" stroke-width="0" fill="#f2f2f2"/>
                    <rect stroke="null" rx="0.5" id="svg_15" height="18" width="3.72222" y="13.5" x="8.81481" stroke-width="0" fill="#f2f2f2"/>
                    <rect stroke="null" rx="1" id="svg_16" height="4.77778" width="3.62963" y="20.11111" x="2.91935" stroke-width="0" fill="#f2f2f2"/>
                    <rect id="svg_21" height="0.08065" width="0" y="16.93548" x="73.79032" stroke-width="0" stroke="null" fill="#ffffff"/>
                    <rect stroke="null" rx="1" id="svg_28" height="4.77778" width="3.62963" y="20.11111" x="38.18519" stroke-width="0" fill="#f2f2f2"/>
                    <rect stroke="null" rx="0.5" id="svg_29" height="18" width="3.72222" y="13.5" x="32.46296" stroke-width="0" fill="#f2f2f2"/>
                    <rect stroke="null" rx="0.5" id="svg_30" height="11" width="3.35185" y="17" x="26.64815" stroke-width="0" fill="#f2f2f2"/>
                 </g>
            </svg>
        </a>
        <a class="link button-podcasts heaven-button-anim-1 heaven-button" href="/">Главная</a>
        <a class="link button-messages heaven-button-anim-1 heaven-button" href="/messages">Сообщения</a>
        <a class="link button-subscriptions heaven-button-anim-1 heaven-button" href="/subscriptions">Подписки</a>
        <a class="link button-profile heaven-button" href="/profile" id="img"><img src="<?php
        $login = $_COOKIE['login'];
        if (isset($login)) {
            $avatar_path = sprintf('%s/avatars/%s.png', $_SERVER['DOCUMENT_ROOT'], $login);
            if (file_exists($avatar_path)) {
                echo sprintf('/avatars/%s.png?v=%d', $login, rand(0, 500));
            } else {
                echo "/avatars/1.png";
            }
        } else {
            echo "/avatars/default.png";
        }
        ?>
        " style="width: 45px; height: 45px;"></a>
        <div id="registrationForm" class="hidden registration-form">
            <button id="hideFormButton" class="hideFormButton"></button>
            <form id="registrationFormSend">
                <label for="username">Имя аккаунта:</label><br>
                <input type="text" id="username" name="username" autocomplete="username" required=""><br>
                <label for="login">Логин:</label><br>
                <input type="text" id="login" name="login" required><br>
                <label for="password">Пароль:</label><br>
                <input type="text" id="password" name="password" required><br>
                <label for="mail">Почта:</label><br>
                <input type="text" id="mail" name="mail" required><br>
                <input type="submit" value="Зарегистрироваться">
            </form>
        </div>
        <div id="loginForm" class="hidden registration-form">
            <button id="hideFormButton" class="hideFormButton"></button>
            <form id="loginFormSend">
                <label for="LoginOrMail">Логин или почта:</label><br>
                <input type="text" id="LoginOrMail" name="LoginOrMail"required><br>
                <label for="password">Пароль:</label><br>
                <input type="text" id="password" name="password" required><br>
                <input type="submit" value="Войти">
            </form>
        </div>
    </div>
    <div class="earth" id="earth">
        <ul id="dialogs">
            
        </ul>
        <div class="page-fixer"></div>
        <script src="/auth/nav.js"></script>
        <script src="gen.js"></script>
    </div>
</body>
</html>
