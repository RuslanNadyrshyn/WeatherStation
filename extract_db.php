<?php
include "connect_db.php";    				// З'єднання з файлом connect_db.php

session_start(); 

$count = $_SESSION['count'];
$art = $_SESSION['art'];
$rows = array();
$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count"); // Отримання даних таблиці bme280
while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;
}

echo json_encode($rows);
?>