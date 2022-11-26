<!-- Поле php -->
<!----------------------------------------------------------------------------------------->
<?php
include "connect_db.php"; // З'єднання з файлом connect_db.php
include "scripts.php";

$page = 1; // 1 сторінка
if (isset($_GET['page'])) // Створення змінної Сторінка з URL сторінки
	$page = $_GET['page'];
else
	$page = 1;

if (isset($_GET['count']))
	$count = $_GET['count'];
else
	$count = 20; // Кількість записів для виводу таблиці та графіку

$art = ($page * $count) - $count; // Визначення початкового значення для діапазону

session_start();
$_SESSION['count'] = $count;
$_SESSION['art'] = $art;



$result = $conn->query("SELECT id_bme280 FROM bme280"); // Запит для визначення кількості записів таблиці bme280
$all_rec = $result->num_rows; // Кількість записів таблиці bme280 
if ($all_rec % $count == 0) // Визначення кількості сторінок навігатора
	$num_of_pages = $all_rec / $count;
else
	$num_of_pages = $all_rec / $count + 1;

// Отримання значень ТЕМПЕРАТУРИ, ТИСКУ, ВИСОТИ, ВОЛОГОСТІ та ДАТИ для графіків
$ch_temp=$conn->query("SELECT temp_bme280 FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count");
$ch_press=$conn->query("SELECT press_bme280 FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count");
$ch_alt=$conn->query("SELECT alt_bme280 FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count");
$ch_hum=$conn->query("SELECT hum_bme280 FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count");             
$ch_date=$conn->query("SELECT date_bme280 FROM bme280 ORDER BY date_bme280 DESC LIMIT $art, $count");

// // Запит до БД для виводу усіх значень з таблиці "bme280"
// $result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");

function echo_count($count, $num)
{ // Функція виводу навігатора кількості рядків з БД 
	if ($count == $num)
		echo '<div class="navigator-item selected" '; //на одній сторінці
	else
		echo '<div class="navigator-item" ';
	echo "onclick=\"window.location='index.php?count=${num}';\">${num}</div>";
}

function count_navigator($count, $all_rec)
{ // Функція виклику різних значень БД
	echo "<div class=\"navigator-block\">Кількість значень: ";
	echo_count($count, 20);
	echo_count($count, 50);
	echo_count($count, 100);
	echo_count($count, $all_rec);
	echo "</div>";
}
// Функція виводу навігатора сторінок БД
function page_navigator($count, $page, $num_of_pages)
{
	echo "<div class='navigator-block'>";
	for ($i = 1; $i <= $num_of_pages; $i++) {
		if ($page == $i)
			echo "<div class=\"navigator-item selected\"";
		else
			echo "<div class=\"navigator-item\"";
		echo "onclick=\"window.location='index.php?page=${i}&count=${count}'\">${i}</div>";
	}
	echo "</div>";
}
?>

<!----------------------------------------------------------------------------------------->
<!-- Поле html -->
<!----------------------------------------------------------------------------------------->
<!doctype html>
<html>

<head>
	<script src="js/chart.min.js"></script>
	<link rel="stylesheet" href="css/style.css">
	<meta charset="utf-8"> <!-- Підключення кирилиці -->
</head>

<body>
	<!-- Блок відображення сторінки -->
	<div class="container">
		
		<h1>Дані датчика BME280</h1> <!-- Створення таблиці "Дані датчика BME280" -->
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
		<?php count_navigator($count, $all_rec); ?>
		<!-- Вивід навігатора сторінок і кількості значень БД -->
		<div class="db-table-container">
			<table id="dbTable"></table>
		</div>
		<?php page_navigator($count, $page, $num_of_pages); ?>
		<!-- Вивід навігатора сторінок БД -->
		<h2>Графіки</h2>
		 <div class="chart-block"> <!--Ініціалізація графіків -->
			<div class="chart-container" onclick="toggleChart({id})" id="container-temp">
				<label class="chart-label">Температура</label>
				<canvas id="chart-temp"></canvas>
			</div>
			<div class="chart-container" onclick="toggleChart({id})" id="container-press">
				<label class="chart-label">Тиск</label>
				<canvas id="chart-press"></canvas>
			</div>
			<div class="chart-container" onclick="toggleChart({id})" id="container-alt">
				<label class="chart-label">Висота над рівнем моря</label>
				<canvas id="chart-alt"></canvas>
			</div>
			<div class="chart-container" onclick="toggleChart({id})" id="container-hum">
				<label class="chart-label">Вологість</label>
				<canvas id="chart-hum"></canvas>
			</div>
		</div>
	</div> <!-- container -->
	<script src="js/utils.js"></script> <!-- Підключення додаткових бібліотек -->
	
	<script src="js/jquery.js"></script>
</body>

</html>


<!----------------------------------------------------------------------------------------->
<!-- Поле script -->
<!----------------------------------------------------------------------------------------->
<script>
	$(document).ready(function () { 										// Скрипт для динамічного оновлення інформації 
		loadData();
		loadDB();
		// loadCharts();
	});
	var loadData = function () {
		$.ajax({														// ajax-запит до бази даних 
			type: "GET",
			url: "/extract.php",											// звертання до файла extract.php 
			dataType: "json",
			success: function (result) {
				$("#temp").text(result.temp_bme280 + ' °С');
				$("#press").text(result.press_bme280 + ' гПа');
				$("#alt").text(result.alt_bme280 + ' м');
				$("#hum").text(result.hum_bme280 + ' %');
				setTimeout(loadData, 2000);
			}
		});
	};
	var loadDB = function () {
		$.ajax({														// ajax-запит до бази даних 
			type: "GET",
			url: "/extract_db.php",										// звертання до файла extract_db.php 
			dataType: "json",
			success: function (result) {
				console.log(result);
				var $table = fillInTable(result);
				$("#dbTable").replaceWith($table);
			}
		});
	};

	// var loadCharts = function () {
	// 	$.ajax({														// ajax-запит до бази даних 
	// 		type: "GET",
	// 		url: "/extract_charts.php",										// звертання до файла extract_db.php 
	// 		dataType: "json",
	// 		success: function (result) {
	// 			var $table = fillInTable(result);
	// 			$("#dbTable").replaceWith($table);
	// 		}
	// 	});
	// };

	var data = [];														// Допоміжний масив для відображення графіків
	// var labels = fetchArray($ch_date, 'date_bme280');					// Заповнення масиву даними з БД для відображення графіків
	// var data = [];	
			data[0] = [<?php while($t=mysqli_fetch_array($ch_temp)){echo '"'.$t['temp_bme280'].'",';}?>].slice(0, -1);
			data[1] = [<?php while($p=mysqli_fetch_array($ch_press)){echo '"'.$p['press_bme280'].'",';}?>].slice(0, -1);
			data[2] = [<?php while($l=mysqli_fetch_array($ch_alt)){echo '"'.$l['alt_bme280'].'",';}?>].slice(0, -1);
			data[3] = [<?php while($h=mysqli_fetch_array($ch_hum)){echo '"'.$h['hum_bme280'].'",';}?>].slice(0, -1);
			var labels = [<?php while ($o = mysqli_fetch_array($ch_date)) { echo '"' . $o['date_bme280'] . '",';}?>].slice(0, -1);
	drawCharts(data, labels); // виклик функції з scripts.php відображення графіків
</script>