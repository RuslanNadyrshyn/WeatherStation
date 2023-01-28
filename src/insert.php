<?php
include "connect_db.php";    							// З'єднання з файлом connect_db.php
include "../config/env/.env.php";

date_default_timezone_set("Europe/Kyiv");
$apiToken = '5937017661:AAED742kwRVWtcu1ci5Ro8KtO7P07OtPRjo';
$chat_id = '-1001824604451';

function send_interval ($time, $chat_id, $apiToken) {
	$last = new DateTime($time);						// Send query to Telegram bot with message:
	$current = new DateTime("now");
	$interval = $last->diff($current);

	$message = $interval->format('Connection restored after %H:%i:%s');

	$data = [
		'chat_id' => $chat_id,
		'text' => $message
	];
	$response = file_get_contents("https://api.telegram.org/bot$apiToken/sendMessage?" .
									   http_build_query($data) );
	shell_exec("cd src; setsid check_connection.sh");
}

if (isset($_GET['temp']))								
	$temp = $_GET['temp'];                        		// Створення змінної Температура з URL сторінки	
if (isset($_GET['press']))
	$press = $_GET['press'];                       		// Створення змінної Тиск з URL сторінки
if (isset($_GET['alt']))
	$alt = $_GET['alt'];                           		// Створення змінної Висота з URL сторінки
if (isset($_GET['hum']))
	$hum = $_GET['hum'];                           		// Створення змінної Вологість з URL сторінки
if (isset($_GET['counter']))
	(int)$counter = $_GET['counter'];                  	// Створення змінної Лічильник з URL сторінки								

$query = $conn->query("SELECT time FROM bme280_current");	// Створення запиту до БД


if(mysqli_num_rows($query) != 0) {
	$row = $query->fetch_array(MYSQLI_ASSOC);
	$time=$row["time"];

	if (strtotime("now") - strtotime($time) > 300) { 	// If was no connection more than 5 minutes
		send_interval($time, $chat_id, $apiToken);
	}

	$sql = "UPDATE bme280_current SET temp_bme280 = $temp, press_bme280 = $press, alt_bme280 = $alt, hum_bme280 = $hum";
	mysqli_query($conn, $sql);							// Оновлення даних у таблиці bme280_current
} else {
	$sql = "INSERT INTO bme280_current (temp_bme280, press_bme280, alt_bme280, hum_bme280, max_count) VALUES ($temp, $press, $alt, $hum, 10)";
	mysqli_query($conn, $sql);							// Внесення даних до таблиці
}

$result = $conn->query("SELECT max_count FROM bme280_current LIMIT 1");
$row = $result->fetch_array(MYSQLI_ASSOC);
(int)$maxcount = $row["max_count"];

if ($counter % $maxcount == 0) {                        // Внесення даних до таблиці bme280
    $sql = "INSERT INTO bme280 (temp_bme280, press_bme280, alt_bme280, hum_bme280) VALUES ($temp, $press, $alt, $hum)";
    mysqli_query($conn, $sql);
}
echo $maxcount;
?>