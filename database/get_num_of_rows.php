<?php
include "connect_db.php";    			// З'єднання з файлом connect_db.php

$count = 20;
if (isset($_GET['count']))				// Зчитування з URL змінної $count з кількістю значень, 
	$count = $_GET['count'];			// які будуть виводитися в таблиці Бази даних

$result = $conn->query("SELECT COUNT(id_bme280) as 'rows' FROM bme280;"); // Запит для визначення кількості записів таблиці bme280
$row = $result->fetch_array(MYSQLI_ASSOC);

echo json_encode($row);
?>


