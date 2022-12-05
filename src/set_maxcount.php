<?php
include "connect_db.php";    							// З'єднання з файлом connect_db.php

$max_count = 10;
if (isset($_GET['max_count']))
	$max_count = $_GET['max_count'];                  								

$query = $conn->query("UPDATE bme280_current SET max_count = $max_count");	// Створення запиту до БД
mysqli_query($conn, $sql);
?>
