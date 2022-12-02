<?php
include "env/.env.php";         // Підключення файлу зі змінною $APPID

if (isset($_GET['city']))		// Зчитування змінної city з URL
	$city = $_GET['city'];		

// Запит до API для отримання даних погоди обраного міста
$result = file_get_contents("https://api.openweathermap.org/data/2.5/weather?q=$city&appid=$APPID&units=metric&lang=ua");

echo $result;      				// Вивід даних у форматі json
?>