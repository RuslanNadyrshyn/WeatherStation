<?php
include "connect_db.php";    				// З'єднання з файлом connect_db.php


if (isset($_GET['ledSlider']))								
	$slider = $_GET['ledSlider'];                   // Створення змінної ledSlider з URL сторінки	

$result = $conn->query("UPDATE bme280_current SET slider = $slider");

$result = $conn->query("SELECT * FROM bme280_current");
$row = $result->fetch_array(MYSQLI_ASSOC);	// Отримання рядка з таблиці bme280_current

echo json_encode($row);
?>