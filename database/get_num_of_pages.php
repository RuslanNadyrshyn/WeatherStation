<?php
include "connect_db.php";    			// З'єднання з файлом connect_db.php

$count = 20;
if (isset($_GET['count']))				// Зчитування з URL змінної $count з кількістю значень, 
	$count = $_GET['count'];			// які будуть виводитися в таблиці Бази даних

$result = $conn->query("SELECT (COUNT(id_bme280) / $count) as 'num_of_pages' FROM bme280;"); // Запит для визначення кількості записів таблиці bme280
$row = $result->fetch_array(MYSQLI_ASSOC);

echo ceil($row);
?>


