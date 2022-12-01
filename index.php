<!-- Поле php -->
<!----------------------------------------------------------------------------------------->
<?php
include "database/connect_db.php"; 		// З'єднання з файлом connect_db.php
include "scripts.php";					// Підключення файлу scripts.php з реалізацією функцій 
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
                    <a class="dropdown-title" id="location"></a>
                    <i class="fa fa-caret-down"></i>
                    <nav class="dropdown-content">
                        <a onclick="changeLocation('Київ')">Київ</a>
                        <a onclick="changeLocation('Львів')">Львів</a>
                        <a onclick="changeLocation('Харків')">Харків</a>
                    </nav>
                </div>
                <div class="dropdown-weather">
                    <div id="location-weather"> °С</div>
					<img class="weather-icon" id="weather-icon" src=""/>
                    <div class="weather-block">
						<img class="weather-content-icon" id="weather-content-icon" src=""/>
                        <table id="weatherTable"></table>
                        <a href="https://www.meteo.gov.ua/">Докладніше</a>
                    </div>
                </div>
            </div>
            <div class="nav-menu">						<!-- Блок навігації по сторінці-->
                <a href="#current">Дані датчика BME280</a>
                <a href="#db">База даних</a>
                <a href="#charts">Графіки</a>
            </div>
        </nav>
    </header>
	
	<div class="container">
		<h1 id="current">Дані датчика BME280</h1> <!-- Створення таблиці "Дані датчика BME280" -->
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
		<h1 id="db">База даних</h1>
		<div class="navigator-container">
			<div class="order-menu">
				<div class="label-col">
					<label>Відображено</label>	
					<label>результатів:</label>
				</div>
				<div class="nav-counter" id="navCounter"></div>
			</div>
			<label id="counter"></label>

			<div class="order-menu">
				<div class="order">
					<label form="param">Сортувати по: </label>
					<select class="select" name="param" id="param" onchange="updateTable()">
						<option value="id">Id</option>
						<option value="date">Час</option>
						<option value="temp">Температура</option>
						<option value="press">Тиск</option>
						<option value="alt">Висота</option>
						<option value="hum">Вологість</option>
					</select>
				</div>
				<div class="order">
					<label form="order">Порядок:</label>
					<select class="select" name="order" id="order" onchange="updateTable()">
						<option value="DESC">По спаданню</option>
						<option value="ASC">По зростанню</option>
					</select>
				</div>
			</div>
		</div>
		<div class="db-table-container">
			<table id="dbTable"></table>
		</div>
		<!-- Вивід навігатора сторінок БД -->
		<div id="navPages"></div>
		<label style="float: right;">Сторінка
			<label id="page"></label>
			<label>/</label>
			<label id="numOfPages"></label>
		</label>
		<h1 id="charts">Графіки</h1>	
		<label class="chart-note">*Натисніть на графік для його збільшення</label>			
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
	</div> <!-- /container -->
	<script src="js/utils.js"></script> 				<!-- Підключення додаткових бібліотек -->
	<script src="js/chart.min.js"></script>
	<script src="js/jquery.js"></script>
</body>
</html>

<!----------------------------------------------------------------------------------------->
<!-- Поле script -->
<!----------------------------------------------------------------------------------------->
<script>
	var city = "Київ";
	var page = 1;
	var count = 20;
	var param = "date";
	var order = "DESC";
	
	$(document).ready(function () { 					// Функція для динамічного оновлення інформації 
		loadData();										// в таблиці "Дані датчика BME280"
		changeLocation(city);							// Виклик функції для створення таблиці з даними погоди									
		loadTable (page, count, param, order);
		printNavCounter();
		printNavPages(count);
	});

	
</script>