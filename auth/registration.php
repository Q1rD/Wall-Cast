<?php
function generateSalt($length = 32) {
    return bin2hex(random_bytes($length / 2));
}

function generateAuthToken($length = 16) {
    return bin2hex(random_bytes($length / 2));
}


if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $username = filter_input(INPUT_POST, 'username', FILTER_SANITIZE_STRING);
    $login = filter_input(INPUT_POST, 'login', FILTER_SANITIZE_STRING);
    $userPassword = filter_input(INPUT_POST, 'password', FILTER_SANITIZE_STRING);
    $mail = filter_input(INPUT_POST, 'mail', FILTER_SANITIZE_EMAIL);
    $is_verifed = 0;
    
    if (!empty($username) && !empty($login) && !empty($userPassword) && !empty($mail)) {
        $servername = "localhost";
        $db_username = "mainUser";
        $db_password = "AlpqeicFBPagd0193H";
        $dbname = "main";
        
        $conn = new mysqli($servername, $db_username, $db_password, $dbname);
        
        if ($conn->connect_error) {
            die("Ошибка подключения к базе данных: " . $conn->connect_error);
        }
        
        $sql_check = "SELECT * FROM Users WHERE login = ?";
        $stmt_check = $conn->prepare($sql_check);
        $stmt_check->bind_param("s", $login);
        $stmt_check->execute();
        $result_check = $stmt_check->get_result();
        
        if ($result_check->num_rows === 0) {
            $salt = generateSalt();
            $passwordWithSalt = $userPassword . $salt;
            $password = password_hash($passwordWithSalt, PASSWORD_DEFAULT);
            $sql_insert = "INSERT INTO Users (user_name, login, password, mail) VALUES (?, ?, ?, ?)";
            $stmt_insert = $conn->prepare($sql_insert);
            $stmt_insert->bind_param("ssss", $username, $login, $password, $mail);
            if ($stmt_insert->execute()) {
                $sql_get_userid = "SELECT user_id FROM Users WHERE login = ?";
                $stmt_get_userid = $conn->prepare($sql_get_userid);
                $stmt_get_userid->bind_param("s", $login);
                $stmt_get_userid->execute();
                $user_id = $stmt_get_userid->get_result();
                $user_id_row = $user_id->fetch_assoc();
                $user_id = $user_id_row['user_id'];
                
                $timestamp = microtime(true);
                $auth_token = substr(hash('sha256', $login . $timestamp . $password), 0, 30);
                
                $sql_insert_authtoken = "UPDATE Auth SET auth_token = ?, password_salt = ? WHERE user_id = ?";
                $stmt_insert_authtoken = $conn->prepare($sql_insert_authtoken);
                $stmt_insert_authtoken->bind_param("ssi", $auth_token, $salt, $user_id);
                $stmt_insert_authtoken->execute();

                $result = array("authtoken" => $auth_token, "login" => $login, "username" => $username, "userid" => $user_id);
                echo json_encode($result);
            } else {
                echo 500;
            }
        } else {
            echo "login_busy";
        }
        $conn->close();
    } else {
        echo 400;
    }
}
?>
