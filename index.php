<!-- Поле php -->
<!----------------------------------------------------------------------------------------->
<?php
include "connect_db.php"; 				// З'єднання з файлом connect_db.php
include "scripts.php";					// Підключення файлу scripts.php з реалізацією функцій 

$page = 1; 								// 1 сторінка по дефолту
$count = 20; 							// Кількість записів для виводу таблиці та графіку
if (isset($_GET['page'])) 				// Зчитування з URL змінної $page з номером сторінки
	$page = $_GET['page'];

if (isset($_GET['count']))				// Зчитування з URL змінної $count з кількістю значень, 
	$count = $_GET['count'];			// які будуть ви водитися в таблиці Бази даних

$start = ($page * $count) - $count; 	// Визначення початкового значення для діапазону

$result = $conn->query("SELECT id_bme280 FROM bme280"); // Запит для визначення кількості записів таблиці bme280
$all_rec = $result->num_rows; 			// Кількість записів таблиці bme280 
if ($all_rec % $count == 0) 			// Визначення кількості сторінок навігатора
	$num_of_pages = $all_rec / $count;
else
	$num_of_pages = $all_rec / $count + 1;

$rows = array();
if($count == -1) 						// Отримання усіх даних таблиці bme280
	$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC"); 
else									// Отримання даних таблиці bme280 відповідно вказаній кількості та номеру сторінки
	$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC LIMIT $start, $count"); 
while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;
}

function echo_count($count, $num) { 						// Функція виводу для кожного значення кількості рядків
	if ($count == $num)
		echo '<a class="navigator-item selected" '; 		// Стиль для обраного значення
	else
		echo '<a class="navigator-item" ';					// Стиль для необраного значення
	if($num == -1) 
		echo "href='index.php?count=${num}'>Всі</a>";		// Відображення "Всі" для усіх рядків
	else echo "href='index.php?count=${num}'>${num}</a>"; 	// Відображення значення для кількості рядків
}

function count_navigator($count) {							// Функція виводу навігатора кількості рядків з БД 
	echo "<nav class=\"navigator-block\">";
	echo "<p class=\"navigator-label\">Кількість значень:</p>";
	echo_count($count, 20);
	echo_count($count, 50);
	echo_count($count, 100);
	echo_count($count, 500);
	echo_count($count, 1000);
	echo_count($count, -1);									// Вивід усіх наявних значень
	echo "</nav>";
}

function page_navigator($count, $page, $num_of_pages) {		// Функція виводу навігатора сторінок БД
	if($count < 0) return;
	echo "<nav class='navigator-block'>";
	for ($i = 1; $i <= $num_of_pages; $i++) {
		if ($page == $i)
			echo "<a class=\"navigator-item selected\"";
		else
			echo "<a class=\"navigator-item\" ";
		echo "href=\"index.php?page=${i}&count=${count}\">${i}</a>";
	}
	echo "</nav>";
}
?>

<!----------------------------------------------------------------------------------------->
<!-- Поле html -->
<!----------------------------------------------------------------------------------------->
<!doctype html>
<html>

<head>
	<link rel="stylesheet" href="css/style.css">		<!-- Підключення таблиці стилей-->
	<meta charset="utf-8"> 								<!-- Підключення кирилиці -->
</head>

<body>													<!-- Блок відображення сторінки -->
	<header>
        <nav>
            <div class="weather-container">
                <div class="dropdown">
                    <a id="location">Львів</a>
                    <nav class="dropdown-content">
                        <a onclick="changeLocation('Київ')">Київ</a>
                        <a onclick="changeLocation('Львів')">Львів</a>
                        <a onclick="changeLocation('Харків')">Харків</a>
                    </nav>
                </div>
                <div class="dropdown-weather">
                    <div id="location-weather">-1</div>
                    <div class="weather-block">
                        <div class="weather-content">
                            <table id="weatherTable"></table>
                            <a href="https://www.meteo.gov.ua/">Докладніше</a>
                        </div>
                    </div>
                </div>
            </div>
            <div class="nav-menu">						<!-- Блок навігації по сторінці-->
                <a href="#current-label">Дані датчика BME280</a>
                <a href="#db-label">База даних</a>
                <a href="#charts-label">Графіки</a>
            </div>
        </nav>
    </header>
	
	<div class="container">
		<h1 id="current-label">Дані датчика BME280</h1> <!-- Створення таблиці "Дані датчика BME280" -->
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
		<h1 id="db-label">База даних</h1>
		<?php count_navigator($count); ?> 				<!-- Вивід навігатора сторінок і кількості значень БД -->
		<div class="db-table-container">
			<table id="dbTable"></table>
		</div>
		<?php page_navigator($count, $page, $num_of_pages); ?>
		<!-- Вивід навігатора сторінок БД -->
		<h1 id="charts-label">Графіки</h1>
		<div class="chart-block"> 						<!--Вивід графіків -->
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
	<script src="js/utils.js"></script> 				<!-- Підключення додаткових бібліотек -->
	<script src="js/chart.min.js"></script>
	<script src="js/jquery.js"></script>
</body>
</html>

<!----------------------------------------------------------------------------------------->
<!-- Поле script -->
<!----------------------------------------------------------------------------------------->
<script>
	$(document).ready(function () { 					// Функція для динамічного оновлення інформації 
		loadData();										// в таблиці "Дані датчика BME280"
	});

	var data = <?php 
		echo json_encode($rows);
	?>;
	
	var weatherData = [
            {
                title: "Ранок",
                temp: 15 + " °С",
                hum: 60 + " %"
            },
            {
                title: "День",
                temp: 15 + " °С",
                hum: 60 + " %"
            },
            {
                title: "Вечір",
                temp: 15 + " °С",
                hum: 60 + " %"
            },
            {
                title: "Ніч",
                temp: 15 + " °С",
                hum: 60 + " %"
            },
            {
                title: "Зараз",
                temp: 15 + " °С",
                hum: 60 + " %"
            },
        ];

	printWeather(weatherData);							// Виклик функції для створення таблиці з даними погоди
	printDB(data);										// Виклик функції для створення таблиці "База даних"
	printCharts(data);									// Виклик функції для створення графіків
</script>