<?php
include "connect_db.php";    										// З'єднання з файлом connect_db.php
include "scripts.php";

$page=1;                                        // 1 сторінка
if (isset($_GET['page']))                       // Створення змінної Сторінка з URL сторінки
	$page = $_GET['page'];
else $page = 1;

if(isset($_GET['count'])) 
	$count = $_GET['count'];
else $count = 20;                               // Кількість записів для виводу таблиці та графіку

$art = ($page * $count) - $count;								// Визначення початкового значення для діапазону

// Запит для визначення кількості записів таблиці bme280
$result = $conn->query("SELECT id_bme280 FROM bme280");  	
$all_rec = $result->num_rows;                   // Кількість записів таблиці bme280 
if($all_rec % $count == 0)											// Визначення кількості сторінок навігатора
	$num_of_pages = $all_rec/$count;
else $num_of_pages = $all_rec/$count+1;

// Отримання значень ТЕМПЕРАТУРИ, ТИСКУ, ВИСОТИ, ВОЛОГОСТІ та ДАТИ для графіків
	$ch_temp=$conn->query("SELECT temp_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	$ch_press=$conn->query("SELECT press_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	$ch_alt=$conn->query("SELECT alt_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	$ch_hum=$conn->query("SELECT hum_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");             
	$ch_date=$conn->query("SELECT date_bme280 FROM bme280 ORDER BY date_bme280 LIMIT $art, $count");

	// Функція виводу навігатора кількості значень БД
	function echoCount($count, $num) {
		if($count==$num) echo '<div class="navigator-item selected" ';
		else echo '<div class="navigator-item" ';
		echo "onclick=\"window.location='index.php?count=";
		if($count==$all_rec) echo "${all_rec}'\">${all_rec}</div>"
		else echo "${num}';\">${num}</div>"
	}

	function Count_navigator($count, $all_rec){
		echo "<div class=\"navigator-block\">Кількість значень: ";         
		echoCount($count, 20);
		echoCount($count, 50);
		echoCount($count, 100);
		echoCount($count, $all_rec);
		echo "</div>"; 
	}
	// Функція виводу навігатора сторінок БД
	function Page_navigator($count, $page, $num_of_pages) {
		echo '<div class="navigator-block">';   
		for ($i = 1; $i <= $num_of_pages; $i++){
			if($page==$i) 
				echo "<div class=\"navigator-item-selected\"";
			else echo "<div class=\"navigator-item\"";
			echo "onclick=\"window.location='index.php?page=${i}&count=${count}'>${i}</div>"
		}
		echo "</div>"; 
	}

?>

<!-- Поле script -->
<script>	// Скрипт для динамічного оновлення інформації у таблиці "Дані датчика BME280" 
$(document).ready(function(){
	loadData();
});
var loadData = function() {
	$.ajax({						// ajax-запит до бази даних 
		type:"GET",
		url:"/extract.php",			// звертання до файла extract.php 
		dataType: "json",
		success: function(result){
			$("#temp").text(result.temp_bme280 + ' °С');					
			$("#press").text(result.press_bme280 + ' гПа');
			$("#alt").text(result.alt_bme280 + ' м');
			$("#hum").text(result.hum_bme280 + ' %');
			setTimeout(loadData, 2000); 
		}
	});
};	

function fetchArray(array, value) {		// функція для розбиття строки на масив
	return [
			<?php 
				while($t=mysqli_fetch_array(array)){
					echo '"'.$t[value].'",';
				}
			?>
		].slice(0, -1);
}


var data = [];							// Допоміжний масив для графіків

data[0] = fetchArray($ch_temp, 'temp_bme280');
data[1] = fetchArray($ch_press, 'press_bme280');
data[2] = fetchArray($ch_alt, 'alt_bme280');
data[3] = fetchArray($ch_hum, 'hum_bme280');
var labels = fetchArray($ch_date, 'date_bme280');

drawCharts(data, labels);
</script>

<!doctype html>  <!-- html код -->
<html>
	<head>
		<script src="js/utils.js"></script>			<!-- Підключення додаткових бібліотек -->
		<script src="js/Chart.min.js"></script>	
		<script src="js/jquery.js"></script>
		<link rel="stylesheet" href="css/style.css">
		<meta charset="utf-8">					<!-- Підключення кирилиці -->
	</head>

	<body>									<!-- Блок відображення сторінки -->
		<div class="container">
			<!-- Створення таблиці "Дані датчика BME280" -->
			<h1>Дані датчика BME280</h1>
			<table class="current-table" cellspacing="0">
				<tr>
					<th>Температура</th>
					<th>Тиск</th>
					<th>Висота над рівнем моря</th>
					<th>Вологість</th>
				</tr>
				<tr>
					<td class="current-table-td" id="temp"></td>
					<td class="current-table-td" id="press"></td>
					<td class="current-table-td" id="alt"></td>
					<td class="current-table-td" id="hum"></td>
				</tr>	
			</table>

			<h1>База даних</h1>

			<!-- Вивід навігатора сторінок і кількості значень БД -->
			<?php Count_navigator($count, $all_rec); ?>
			
			<!-- Створення таблиці "База даних"-->	
			<div class="db-table-container">
			<table cellspacing="0">
			<tr">		<!-- Фон першого рядка таблиці-->
				<th>ID</th>
				<th>Дата</th>
				<th>Час</th>
				<th>Температура</th>
				<th>Тиск</th>
				<th>Висота</th>
				<th>Вологість</th>
			</tr>

			<?php 
			// Запит до БД для виводу усіх значень з таблиці "bme280"
			$result=$conn->query("SELECT * FROM bme280 ORDER BY id_bme280 LIMIT $art,$count");           
			while($myrow=$result->fetch_array(MYSQLI_ASSOC)){						// Вивід усіх значень таблиці
				$php_date = date("d/m/Y", strtotime($myrow['date_bme280']));     	// Перевод дати у формат "d/m/Y"
				$php_time = date("H:i:s", strtotime($myrow['date_bme280']));     	// Перевод часу у формат "H:i:s"
				echo"<tr><td>";
				echo $myrow['id_bme280'];
				echo"</td><td>";
				echo $php_date;
				echo"</td><td>";
				echo $php_time;
				echo"</td><td>";
				echo $myrow['temp_bme280']." &degС";
				echo"</td><td>";
				echo $myrow['press_bme280']." гПа";
				echo"</td><td>";
				echo $myrow['alt_bme280']." м";
				echo"</td><td>";
				echo $myrow['hum_bme280']." %";
				echo"</td></tr>";
				}
			?>
			</table>
			<!-- Вивід навігатора сторінок БД -->
			<?php Page_navigator($count, $page, $num_of_pages); ?>

			<caption><h2>Графіки</h2></caption> 
			<div class="chart-container"> 	<!-- Ініціалізація графіків -->
				<div class="chart-container"> 
					<canvas id="chart-temp"></canvas>
				</div>
				<div class="chart-container">
					<canvas id="chart-press"></canvas>
				</div>
				<div class="chart-container">
					<canvas id="chart-alt"></canvas>
				</div>
				<div class="chart-container">
					<canvas id="chart-hum"></canvas>
				</div>
			</div>
		</div> <!-- container -->
	</body>
</html>
