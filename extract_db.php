<?php
include "connect_db.php";    				// З'єднання з файлом connect_db.php
include "index.php";

$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 LIMIT $art, $count"); // Отримання даних таблиці bme280

echo json_encode($result);
?>