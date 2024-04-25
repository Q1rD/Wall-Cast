<?php
require 'vendor/autoload.php';

use \Ratchet\MessageComponentInterface;
use \Ratchet\ConnectionInterface;
use \Ratchet\Server\IoServer;
use \Ratchet\Http\HttpServer;
use \Ratchet\WebSocket\WsServer;

// Класс создания подключения к DB
class MyWebSocketServer {
    private $dbConnection;

    public function __construct(mysqli $dbConnection) {
        $this->dbConnection = $dbConnection;
    }
}

$servername = "localhost";
$db_username = "mainUser";
$db_password = "AlpqeicFBPagd0193H";
$dbname = "main";
$dbConnection = new mysqli($servername, $db_username, $db_password, $dbname);
$webSocketServer = new MyWebSocketServer($dbConnection);

// Класс сервера
class Chat implements MessageComponentInterface {
    private $clients;
    private $dbConnection;

    // Конструктор
    public function __construct(mysqli $dbConnection) {
        $this->clients = new \SplObjectStorage;
        $this->dbConnection = $dbConnection;
    }

    // Обработка подключения нового клиента
    public function onOpen(ConnectionInterface $conn) {
        // Добавление нового клиента
        $this->clients->attach($conn);
        
        // Добавляем в DB
        $sql_add = "INSERT INTO user_connections (user_id, resource_id) VALUES (?, ?)";
        $stmt = $this->dbConnection->prepare($sql_add);
        
        $queryString = $conn->httpRequest->getUri()->getQuery();
        parse_str($queryString, $queryParams);
        $userId = $queryParams['userId'] ?? null;
        $resourceId = $conn->resourceId;

        $stmt->bind_param("is", $userId, $resourceId);
        $stmt->execute();

        echo "New connection! (resource ID: {$conn->resourceId}, user ID: {$userId})\n";
    }


    // Обработка получения нового сообщения от клиента
    public function onMessage(ConnectionInterface $from, $msg) {
        // Распарсить сообщение и извлечь информацию о получателе
        $data = json_decode($msg, true);
        $userId = $data['recipientUserId'];
        $message = $data['message'];
        $dialog_id = $data['dialog_id'];
        $sender = $data['senderID'];

        // Получаем из DB id получателя
        $sql_get = "SELECT resource_id FROM user_connections WHERE (user_id = ?)";
        $stmt_get = $this->dbConnection->prepare($sql_get);

        $stmt_get->bind_param("i", $userId);
        $stmt_get->execute();

        $result = $stmt_get->get_result();
        $row = $result->fetch_assoc();

        $recipients = array();

        while ($row = $result->fetch_assoc()) {
            $recipients[] = $row['resource_id'];
            echo $row['resource_id']."\n";
        }

        $sql_get_dialog = "SELECT message_id, timestamp
        FROM Messages
        WHERE dialog_id = ?
        ORDER BY timestamp DESC
        LIMIT 1;";
        $stmt_get_dialog = $this->dbConnection->prepare($sql_get_dialog);
        
        $stmt_get_dialog->bind_param("i", $dialog_id);
        $stmt_get_dialog->execute();

        $result_dialog = $stmt_get_dialog->get_result();
        $row_dialog = $result_dialog->fetch_assoc();

        $message_id = $row_dialog['message_id'];
        $timestamp = $row_dialog['timestamp'];

        echo "All recipients:\n";
        print_r($recipients);
        echo "Sender: {$sender}; Recipient: {$userId}; message: {$message}\n";

        $data = array('message' => $message, 'recipient' => $userId, 'message_id' => $message_id, 'timestamp' => $timestamp);

        // Отправить сообщение только указанному пользователю
        foreach ($this->clients as $client) {
            // Проверить, является ли текущий клиент получателем сообщения
            if (in_array($client->resourceId, $recipients)) {
                $client->send(json_encode($data));
                echo "Нашёлся!\n";
            }
        }
    }

    // Обработка отключения клиента
    public function onClose(ConnectionInterface $conn) {
        // Удаляем отключенного клиента из списка
        $this->clients->detach($conn);

        $sql_del_user = "DELETE FROM user_connections WHERE (resource_id = ?)";
        $stmt_del_user = $this->dbConnection->prepare($sql_del_user);
        $resource_id = $conn->resourceId;
        $stmt_del_user->bind_param("i", $resource_id);
        $stmt_del_user->execute();        

        echo "Connection {$conn->resourceId} has disconnected\n";
    }

    // Обработка ошибок
    public function onError(ConnectionInterface $conn, \Exception $e) {
        echo "An error has occurred: {$e->getMessage()}\n";
        $conn->close();
    }
}

// Создаем сервер и запускаем его
$server = IoServer::factory(
    new HttpServer(
        new WsServer(
            new Chat($dbConnection)
        )
    ),
    8080 // Порт сервера
);

echo "Server running on port 8080...\n";

$server->run();

?>