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

// Запит до БД для виводу усіх значень з таблиці "bme280"
$rows = array();
if($count == -1) 
	$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC"); // Отримання даних таблиці bme280;
else
	$result = $conn->query("SELECT * FROM bme280 ORDER BY id_bme280 DESC LIMIT $art, $count"); // Отримання даних таблиці bme280

while($r=$result->fetch_array(MYSQLI_ASSOC)) {
    $rows[] = $r;
}


function echo_count($count, $num)
{ // Функція виводу навігатора кількості рядків з БД 
	if ($count == $num)
		echo '<a class="navigator-item selected" '; //на одній сторінці
	else
		echo '<a class="navigator-item" ';
	if($num == -1) echo "href='index.php?count=${num}'>Всі</a>";
	else echo "href='index.php?count=${num}'>${num}</a>";

}

function count_navigator($count)
{ // Функція виклику різних значень БД
	echo "<nav class=\"navigator-block\">";
	echo "<p class=\"navigator-label\">Кількість значень:</p>";
	echo_count($count, 20);
	echo_count($count, 50);
	echo_count($count, 100);
	echo_count($count, 500);
	echo_count($count, 1000);
	echo_count($count, -1);
	echo "</nav>";
}
// Функція виводу навігатора сторінок БД
function page_navigator($count, $page, $num_of_pages)
{
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
	<link rel="stylesheet" href="css/style.css">
	<meta charset="utf-8"> <!-- Підключення кирилиці -->
</head>

<body>
	<header>
		<div class="container">
			<nav>
				<a style="margin-left: auto;" href="#current-label">Дані датчика BME280</a>
				<a href="#db-label">База даних</a>
				<a href="#charts-label">Графіки</a>
			</nav>
		</div>
	</header>
	<!-- Блок відображення сторінки -->
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
		<?php count_navigator($count); ?>
		<!-- Вивід навігатора сторінок і кількості значень БД -->
		<div class="db-table-container">
			<table id="dbTable"></table>
		</div>
		<?php page_navigator($count, $page, $num_of_pages); ?>
		<!-- Вивід навігатора сторінок БД -->
		<h1 id="charts-label">Графіки</h1>
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
	<script src="js/chart.min.js"></script>
	<script src="js/jquery.js"></script>
</body>

</html>


<!----------------------------------------------------------------------------------------->
<!-- Поле script -->
<!----------------------------------------------------------------------------------------->
<script>
	$(document).ready(function () { 										// Скрипт для динамічного оновлення інформації 
		loadData();
	});

	var data = <?php 
		echo json_encode($rows);
		?>;
	printDB(data);
	printCharts(data);
</script>