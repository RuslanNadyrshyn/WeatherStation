<?php 
include "connect_db.php"; 		// З'єднання з файлом connect_db.php
/*
1. read params
2. sort query by params
3. return result
*/
$page = 1; 								// 1 сторінка по дефолту
$count = 20; 							// Кількість записів для виводу таблиці та графіку
$param = 'date_bme280';
$order = 'ASC';

if (isset($_GET['page'])) 				// Зчитування з URL змінної $page з номером сторінки
	$page = $_GET['page'];

if (isset($_GET['count']))				// Зчитування з URL змінної $count з кількістю значень, 
	$count = $_GET['count'];			// які будуть виводитися в таблиці Бази даних

if (isset($_GET['param']))			    // 
	$param = $_GET['param'];			// 

if (isset($_GET['order']))			    // 
	$order = $_GET['order'];			// 

$start = ($page * $count) - $count; 	// Визначення початкового значення для діапазону

if($count == -1) 						// Отримання усіх даних таблиці bme280
	$result = $conn->query("SELECT * FROM bme280 ORDER BY ${param}_bme280 ${order}"); 
else									// Отримання даних таблиці bme280 відповідно вказаній кількості та номеру сторінки
	$result = $conn->query("SELECT * FROM (SELECT * FROM bme280 ORDER BY ${param}_bme280 ${order}) as T LIMIT $start, $count;"); 
while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;
}

echo json_encode($rows);
?>