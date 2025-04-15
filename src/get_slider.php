<?php
include "connect_db.php";    			// З'єднання з файлом connect_db.php

if (isset($_GET['slider']))				// Зчитування з URL змінної $slider
	$slider = $_GET['slider'];	

// Get slider value from database
$result = $conn->query("SELECT slider FROM bme280_current LIMIT 1"); 
$row = $result->fetch_array(MYSQLI_ASSOC);

echo json_encode($row);
?>
