<?php

/********************\
//> MADE BY kr7a-J <\\
\********************/

$servername = "localhost";
$db_username = "mainUser";
$db_password = "AlpqeicFBPagd0193H";
$dbname = "main";

// get n -- n+50 messages between user -- user_id
function GETMESSANGES($data) {
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    
    if ($conn->connect_error) {
        die("Error connecting to database: " . $conn->connect_error);
    }
    
    $user_id = $_COOKIE['user-id'];
    $receiver_user_id = $data['user_id'];
    $n = $data['n'];
    
    $sql_get_chats_id = "SELECT dialog_id FROM Dialogs WHERE ((user_id1 = ? AND user_id2 = ?) OR (user_id2 = ? AND user_id1 = ?))";
    $stmt_get_chats_id = $conn->prepare($sql_get_chats_id);
    $stmt_get_chats_id->bind_param("iiii", $user_id, $receiver_user_id, $user_id, $receiver_user_id);
    $stmt_get_chats_id->execute();
    $result_get_chats_id = $stmt_get_chats_id->get_result();
    $row = $result_get_chats_id->fetch_assoc();
    $dialog_id = $row['dialog_id'];
    if ($dialog_id == NULL) {
        return 'none';
    } else {
        $count = 0;
        
        $offset = $n-1;
        $limit = 50;
        
        $sql_get_message_text = "SELECT message_text, sender_id, message_id, timestamp FROM Messages WHERE dialog_id = ? LIMIT $limit OFFSET $offset";
        $stmt_get_message_text = $conn->prepare($sql_get_message_text);
        
        $stmt_get_message_text->bind_param('i', $dialog_id);
        $stmt_get_message_text->execute();
        $result_get_message_text = $stmt_get_message_text->get_result();
        
        $message_texts = array();
        
        while ($row_get_message_text = $result_get_message_text->fetch_assoc()) {
            $message_text = $row_get_message_text['message_text'];
            $sender_id = $row_get_message_text['sender_id'];
            $message_id = $row_get_message_text['message_id'];
            $timestamp = $row_get_message_text['timestamp'];
            $message_texts[$count++] = array("sender" => $sender_id, "timestamp" => $timestamp, "message_id" => $message_id, "message" => $message_text);
        }
        
        $info = array();
        $info['dialog_id'] = $dialog_id;
        $info['users'] = array('1' => $user_id, '2' => $receiver_user_id);
        $all_dialogs_messages = $message_texts;
        $data = array('info' => $info, 'messages' => $all_dialogs_messages);
    }
    $conn->close();
    return json_encode($data);
}
// get last messages
function GETLASTMESSAGE() {
    $count = 0;
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    
    if ($conn->connect_error) {
        die("Error connecting to database: " . $conn->connect_error);
    }
    
    $user_id = $_COOKIE['user-id'];
    
    $sql_get_chats_id = "
        SELECT dialogs_id, user_id1_list, user_id2_list
        FROM (
            SELECT GROUP_CONCAT(DISTINCT dialog_id ORDER BY timestamp DESC SEPARATOR ',') AS dialogs_id,
                   GROUP_CONCAT(CASE WHEN user_id1 != ? THEN user_id1 END ORDER BY timestamp DESC SEPARATOR ',') AS user_id1_list,
                   GROUP_CONCAT(CASE WHEN user_id2 != ? THEN user_id2 END ORDER BY timestamp DESC SEPARATOR ',') AS user_id2_list
            FROM Dialogs
            WHERE user_id1 = ? OR user_id2 = ?
        ) AS subquery;
    ";
    $stmt_get_chats_id = $conn->prepare($sql_get_chats_id);
    $stmt_get_chats_id->bind_param("iiii", $user_id, $user_id, $user_id, $user_id);
    $stmt_get_chats_id->execute();
    $result_get_chats_id = $stmt_get_chats_id->get_result();
    $row = $result_get_chats_id->fetch_assoc();
    $dialogs_id = $row['dialogs_id'];
    $user_id1_list = $row['user_id1_list'];
    $user_id2_list = $row['user_id2_list'];
    $user_id1_array = array_filter(explode(',', $user_id1_list));
    $user_id2_array = array_filter(explode(',', $user_id2_list));
    $user_ids = array_unique(array_merge($user_id1_array, $user_id2_array));

    $stmt_get_chats_id->close();
    if ($dialogs_id == NULL) {
        return 'none';
    } else {
        $dialogs_id_array = explode(',', $dialogs_id);
        
        $all_dialogs_messages = array();
        
        
        foreach ($dialogs_id_array  as $dialog_id) {
            $sql_count_unread = "SELECT COUNT(*) AS unread_count FROM MessageRead WHERE is_read = 0 AND dialog_id = ?";
            $stmt_count_unread = $conn->prepare($sql_count_unread);
            $stmt_count_unread->bind_param("i", $dialog_id);
            $stmt_count_unread->execute();
            $unread_count_ = $stmt_count_unread->get_result();
            $unread_count_ = $unread_count_->fetch_assoc();
            $unread_count = $unread_count_['unread_count'];
            
            $sql_get_message_text = 'SELECT message_text, sender_id, message_id, timestamp FROM Messages WHERE dialog_id = ? ORDER BY timestamp DESC LIMIT 1';
            $stmt_get_message_text = $conn->prepare($sql_get_message_text);
            $stmt_get_message_text->bind_param('i', $dialog_id);
            $stmt_get_message_text->execute();
            $result_get_message_text = $stmt_get_message_text->get_result();
            
            $message_texts = array();
            
            while ($row = $result_get_message_text->fetch_assoc()) {
                $message_text = $row['message_text'];
                $sender_id = $row['sender_id'];
                $message_id = $row['message_id'];
                $timestamp = $row['timestamp'];
                $message_texts[$message_id] = $message_text;
            }
            
            $info = array();
            $info["dialog_id"] = $dialog_id;
            $info["user_2"] = $user_ids[$count];
            $info["unread"] = $unread_count;
            $info["timestamp"] = $timestamp;
            
            $all_dialogs_messages[$count++] = array("info" => $info, "message" => $message_texts);
        }
        return json_encode($all_dialogs_messages);
    }
    $conn->close();
}
// send message
function SENDMESSAGE() {
    $conn = new mysqli($servername, $db_username, $db_password, $dbname);
    
    if ($conn->connect_error) {
        die("Error connecting to database: " . $conn->connect_error);
    }
    
    $user_id1 = $_COOKIE['user-id'];
    $user_id2 = $data['user_id'];
    $message = urldecode($data['message']);
    
    // dialog_id
    $sql_insert = "INSERT INTO Dialogs (user_id1, user_id2) 
                   SELECT ?, ?
                   WHERE NOT EXISTS (
                       SELECT 1 FROM Dialogs
                       WHERE (user_id1 = ? AND user_id2 = ?)
                          OR (user_id1 = ? AND user_id2 = ?)
                   )";
    
    $stmt_insert = $conn->prepare($sql_insert);
    $stmt_insert->bind_param("iiiiii", $user_id1, $user_id2, $user_id1, $user_id2, $user_id2, $user_id1);
    $stmt_insert->execute();
    
    if ($stmt_insert->affected_rows > 0) {
        // new
        $dialog_id = $conn->insert_id;
    } else {
        // old
        $sql_select = "SELECT dialog_id FROM Dialogs
                       WHERE (user_id1 = ? AND user_id2 = ?)
                       OR (user_id1 = ? AND user_id2 = ?)";
        
        $stmt_select = $conn->prepare($sql_select);
        $stmt_select->bind_param("iiii", $user_id1, $user_id2, $user_id2, $user_id1);
        $stmt_select->execute();
        $stmt_select->bind_result($existing_dialog_id);
        $stmt_select->fetch();
        
        $dialog_id = $existing_dialog_id;
        
        $stmt_select->close();
    }
    
    $stmt_insert->close();
    
    // send message
    $sql_send = "INSERT INTO Messages (dialog_id, message_text, sender_id) VALUES (?, ?, ?)";
    $stmt_send = $conn->prepare($sql_send);
    $stmt_send->bind_param("isi", $dialog_id, $message, $user_id1);
    $stmt_send->execute();
    $stmt_send->close();
    $conn->close();
    return 1;
}


$json_data = file_get_contents("php://input");
$data = json_decode($json_data, true);

if ($data !== null && isset($data['operation'])) {
    if (isset($_COOKIE['user-id'])) {
        switch ($data['operation']) {
            case 'GETMESSANGES':
                $res = GETMESSANGES($data);
                break;
            case 'GETLASTMESSAGE':
                $res = GETLASTMESSAGE($data);
                break;
            case 'SENDMESSAGE':
                $res = SENDMESSAGE($data);
                break;
        }
    }
    if (isset($res)) {
        echo $res;
    } else {
        echo 'uncorrect call or need auth';
    }
}

?>