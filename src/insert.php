<?php
include "connect_db.php";    							// З'єднання з файлом connect_db.php

if (isset($_GET['temp']))								
	$temp = $_GET['temp'];                        		// Створення змінної Температура з URL сторінки	
if (isset($_GET['press']))
	$press = $_GET['press'];                       		// Створення змінної Тиск з URL сторінки
if (isset($_GET['alt']))
	$alt = $_GET['alt'];                           		// Створення змінної Висота з URL сторінки
if (isset($_GET['hum']))
	$hum = $_GET['hum'];                           		// Створення змінної Вологість з URL сторінки
if (isset($_GET['counter']))
	$counter = $_GET['counter'];                  		// Створення змінної Лічильник з URL сторінки								

$query = $conn->query("SELECT * FROM bme280_current");	// Створення запиту до БД

if(mysqli_num_rows($query) != 0) {
	$sql = "UPDATE bme280_current SET temp_bme280 = $temp, press_bme280 = $press, alt_bme280 = $alt, hum_bme280 = $hum";
	mysqli_query($conn, $sql);				// Оновлення даних у таблиці bme280_current
}
else {
	$sql = "INSERT INTO bme280_current (temp_bme280, press_bme280, alt_bme280, hum_bme280, max_count) VALUES ($temp, $press, $alt, $hum, 10)";
	mysqli_query($conn, $sql);				// Внесення даних до таблиці
}

$result = $conn->query("SELECT max_count FROM bme280_current LIMIT 1");
$maxcount = $result->fetch_field_direct(0);

if ((int)$counter % (int)$maxcount == 0) {                                                      // Внесення даних до таблиці bme280
        $sql = "INSERT INTO bme280 (temp_bme280, press_bme280, alt_bme280, hum_bme280) VALUES ($temp, $press, $alt, $hum)";
        mysqli_query($conn, $sql);
}
?>