<?php
include "connect_db.php";    							// З'єднання з файлом connect_db.php
include('config/env/.env.php');
$apiToken = '5937017661:AAED742kwRVWtcu1ci5Ro8KtO7P07OtPRjo';
$chat_id = '-1001824604451';

$query = $conn->query("SELECT time FROM bme280_current");	// Створення запиту до БД
$row = $query->fetch_array(MYSQLI_ASSOC);
$time=$row["time"];

if (strtotime("now") - strtotime($time) > 300) {
    $message = "Соединение потеряно $time";
}

$data = [
    'chat_id' => $chat_id,
    'text' => $message
];
$response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" .
                                   http_build_query($data) );

?>