<?php
if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $loginOrMail =  filter_input(INPUT_POST, 'LoginOrMail', FILTER_SANITIZE_STRING);
    $userPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    
    if (!empty($loginOrMail) && !empty($userPassword)) {
        $servername = "localhost";
        $db_username = "mainUser";
        $db_password = "AlpqeicFBPagd0193H";
        $dbname = "main";
        
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        
        if ($conn->connect_error) {
            die("Ошибка подключения к базе данных: " . $conn->connect_error);
        }
        
        if (filter_var($loginOrMail, FILTER_VALIDATE_EMAIL)) {
            // вход по почте
            $sql_get_passwordUserId = "SELECT user_id, user_name, login, password FROM Users WHERE mail = ?";
            $stmt_get_passwordUserId = $conn->prepare($sql_get_passwordUserId);
            $stmt_get_passwordUserId->bind_param("s", $loginOrMail);
            $stmt_get_passwordUserId->execute();
            $passwordUserId = $stmt_get_passwordUserId->get_result();
            $passwordUserId_row = $passwordUserId->fetch_assoc();
            $user_id = $passwordUserId_row['user_id'];
            $user_name = $passwordUserId_row['user_name'];
            $login = $passwordUserId_row['login'];
            $stored_password = $passwordUserId_row['password'];
            
            $sql_get_salt = "SELECT auth_token, password_salt FROM Auth WHERE user_id = ?";
            $stmt_get_salt =  $conn->prepare($sql_get_salt);
            $stmt_get_salt->bind_param("i", $user_id);
            $stmt_get_salt->execute();
            $salt = $stmt_get_salt->get_result();
            $salt_row = $salt->fetch_assoc();
            $authToken = $salt_row['auth_token'];
            $salt = $salt_row['password_salt'];
            
            $passwordWithSalt = $userPassword . $salt;
            
            if (password_verify($passwordWithSalt, $stored_password)) {
                // правильный пароль
                echo $authToken . '|' . $login . '|' . $user_name . "|" . $user_id;
            } else {
                // неправильный пароль
                echo "inc_password";
            }
        } else {
            // вход по логину
            $sql_get_password = "SELECT user_id, user_name, password FROM Users WHERE login = ?";
            $stmt_get_password = $conn->prepare($sql_get_password);
            $stmt_get_password->bind_param("s", $loginOrMail);
            $stmt_get_password->execute();
            $password = $stmt_get_password->get_result();
            if ($password->num_rows > 0) {
                $password_row = $password->fetch_assoc();
                $user_id = $password_row['user_id'];
                $user_name = $password_row['user_name'];
                $stored_password = $password_row['password'];
                
                $sql_get_salt = "SELECT auth_token, password_salt FROM Auth WHERE user_id = ?";
                $stmt_get_salt =  $conn->prepare($sql_get_salt);
                $stmt_get_salt->bind_param("i", $user_id);
                $stmt_get_salt->execute();
                $salt = $stmt_get_salt->get_result();
                $salt_row = $salt->fetch_assoc();
                $salt = $salt_row['password_salt'];
                $auth_token = $salt_row['auth_token'];
                
                $passwordWithSalt = $userPassword . $salt;
                
                if (password_verify($passwordWithSalt, $stored_password)) {
                    // правильный пароль
                    echo $auth_token . '|' . $loginOrMail . '|' . $user_name . "|" . $user_id;
                } else {
                    // неправильный пароль
                    echo "inc_password";
                }
            } else {
                echo "inc_log";
            }
        }
        $conn->close();
    } else {
        echo 400;
    }
}
?>