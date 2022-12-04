<?php 
include "connect_db.php"; 				// З'єднання з файлом connect_db.php

if (isset($_GET['page'])) 				// Зчитування з URL змінних $page, $count, $param та 
	$page = $_GET['page'];				// $order для формування запиту до БД

if (isset($_GET['count']))				
	$count = $_GET['count'];			

if (isset($_GET['param']))		
	$param = $_GET['param'];			

if (isset($_GET['order']))			    
	$order = $_GET['order'];			

$start = ($page * $count) - $count; 	// Визначення початкового значення для діапазону

if($count == -1) // Отримання усіх даних таблиці bme280
	$result = $conn->query("SELECT * FROM (SELECT * FROM bme280 ORDER BY ${param}_bme280 ${order}) as T;"); 
else // Отримання даних таблиці bme280 відповідно вказаній кількості та номеру сторінки
	$result = $conn->query("SELECT * FROM (SELECT * FROM bme280 ORDER BY ${param}_bme280 ${order}) as T ORDER BY ${param}_bme280 ${order} LIMIT $start, $count;"); 
while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;
}

echo json_encode($rows);
?>