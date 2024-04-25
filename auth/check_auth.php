<?php
if (isset($_COOKIE["auth-token"])) {
    $user_authTOKEN = $_COOKIE["auth-token"];
    $user_id = $_COOKIE["user-id"];

    $servername = "localhost";
    $db_username = "mainUser";
    $db_password = "AlpqeicFBPagd0193H";
    $dbname = "main";
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);    
    $sql_get_authTOKEN = "SELECT auth_token FROM Auth WHERE user_id = ?";
    $stmt_get_authTOKEN = $conn->prepare($sql_get_authTOKEN);
    $stmt_get_authTOKEN->bind_param("i", $user_id);
    $stmt_get_authTOKEN->execute();
    $get_authTOKEN = $stmt_get_authTOKEN->get_result();
    $get_authTOKEN_row = $get_authTOKEN->fetch_assoc();
    $auth_token = $get_authTOKEN_row["auth_token"];
    $conn->close();
    $cached_result = ($auth_token == $user_authTOKEN) ? 1 : 0;
    echo $cached_result;
} else {
    echo 0;
}
?>
