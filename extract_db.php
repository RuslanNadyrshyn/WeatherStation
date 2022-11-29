<?php
include "connect_db.php";    				// З'єднання з файлом connect_db.php

session_start();                            // Зчитування змінних $count та $start з сесії
$count = $_SESSION['count'];                
$start = $_SESSION['start'];

$rows = array();                            // Створення масиву для даних з БД 
// Отримання даних таблиці bme280
$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC LIMIT $start, $count"); 
while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;                           // Розділення отриманих даних на рядки
}

echo json_encode($rows);                    // Вивід отриманих рядків у форматі json
?>