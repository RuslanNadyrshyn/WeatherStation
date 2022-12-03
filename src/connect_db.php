<?php
include "../config/env/.env.php";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_NAME, $DB_PASS);  	// Підключення до БД
if (! $conn)
die("Помилка: не вдається підключитися: " . $conn->connect_error);      // Повідомлення при неможливості підключення до БД
?>