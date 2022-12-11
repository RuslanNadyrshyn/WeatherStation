<?php
include "../config/env/.env.php";

$conn = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);  	// Підключення до БД
if (! $conn)                                // Повідомлення при неможливості підключення до БД
    die("Помилка: не вдається підключитися: " . $conn->connect_error);      
?>