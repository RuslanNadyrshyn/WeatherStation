<!-- Поле php -->
<!----------------------------------------------------------------------------------------->
<?php
include "connect_db.php";    										// З'єднання з файлом connect_db.php
// include "scripts.php";

$page=1;                                        					// 1 сторінка
if (isset($_GET['page']))                       					// Створення змінної Сторінка з URL сторінки
	$page = $_GET['page'];
else $page = 1;

if(isset($_GET['count'])) 
	$count = $_GET['count'];
else $count = 20;                               					// Кількість записів для виводу таблиці та графіку

$art = ($page * $count) - $count;									// Визначення початкового значення для діапазону

$result = $conn->query("SELECT id_bme280 FROM bme280");  			// Запит для визначення кількості записів таблиці bme280
$all_rec = $result->num_rows;                   					// Кількість записів таблиці bme280 
if($all_rec % $count == 0)											// Визначення кількості сторінок навігатора
	$num_of_pages = $all_rec/$count;
else $num_of_pages = $all_rec/$count+1;

// Отримання значень ТЕМПЕРАТУРИ, ТИСКУ, ВИСОТИ, ВОЛОГОСТІ та ДАТИ для графіків
	// $ch_temp=$conn->query("SELECT temp_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	// $ch_press=$conn->query("SELECT press_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	// $ch_alt=$conn->query("SELECT alt_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	// $ch_hum=$conn->query("SELECT hum_bme280 FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");             
	// $ch_date=$conn->query("SELECT date_bme280 FROM bme280 ORDER BY date_bme280 LIMIT $art, $count");

	// // Запит до БД для виводу усіх значень з таблиці "bme280"
	$result=$conn->query("SELECT * FROM bme280 ORDER BY id_bme280 LIMIT $art, $count");
	
	function echo_count($count, $num, $all_rec) {								// Функція виводу навігатора кількості рядків з БД 
		if($count==$num) echo '<div class="navigator-item selected" '; //на одній сторінці
		else echo '<div class="navigator-item" ';
		echo "onclick=\"window.location='index.php?count=";
		if($count==$all_rec) echo "${all_rec}'\">${all_rec}</div>";
		else echo "${num}';\">${num}</div>";
	}

	function count_navigator($count, $all_rec){						// Функція виклику різних значень БД
		echo "<div class=\"navigator-block\">Кількість значень: ";         
		echo_count($count, 20, $all_rec);
		echo_count($count, 50, $all_rec);
		echo_count($count, 100, $all_rec);
		echo_count($count, $all_rec, $all_rec);
		echo "</div>"; 
	}
	// Функція виводу навігатора сторінок БД
	function page_navigator($count, $page, $num_of_pages) {
		echo '<div class="navigator-block">';   
		for ($i = 1; $i <= $num_of_pages; $i++){
			if($page==$i) 
				echo "<div class=\"navigator-item-selected\"";
			else echo "<div class=\"navigator-item\"";
			echo "onclick=\"window.location='index.php?page=${i}&count=${count}'>${i}</div>";
		}
		echo "</div>"; 
	}

	function print_db_table($result) {
		while($myrow=$result->fetch_array(MYSQLI_ASSOC)){						// Вивід усіх значень таблиці
			$php_date = date("d/m/Y", strtotime($myrow['date_bme280']));     	// Перевод дати у формат "d/m/Y"
			$php_time = date("H:i:s", strtotime($myrow['date_bme280']));     	// Перевод часу у формат "H:i:s"
			echo "<tr>";
			echo "<td>".$myrow['id_bme280']."</td>";
			echo "<td>".$php_date."</td>";
			echo "<td>".$php_time."</td>";
			echo "<td>".$myrow['temp_bme280']." &degС</td>";
			echo "<td>".$myrow['press_bme280']." гПа</td>";
			echo "<td>".$myrow['alt_bme280']." м</td>";
			echo "<td>".$myrow['hum_bme280']." %</td>";
			echo "</tr>";
		}
	}
?>

<!----------------------------------------------------------------------------------------->
<!-- Поле html -->
<!----------------------------------------------------------------------------------------->
<!doctype html>
<html>
	<head>
	
	
		<link rel="stylesheet" href="css/style.css">
		<meta charset="utf-8">										<!-- Підключення кирилиці -->
	</head>

	<body>															<!-- Блок відображення сторінки -->
		<div class="container">
			<!-- <div class="db-table-container">
				<table id="dbTable"></table>
			</div>					 -->
			<h1>Дані датчика BME280</h1>							<!-- Створення таблиці "Дані датчика BME280" -->
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
			<?php count_navigator($count, $all_rec); ?>				<!-- Вивід навігатора сторінок і кількості значень БД -->				
			<div class="db-table-container">						<!-- Створення таблиці "База даних"-->	
				<table cellspacing="0">
					<tr">											<!-- Фон першого рядка таблиці-->
						<th>ID</th>
						<th>Дата</th>
						<th>Час</th>
						<th>Температура</th>
						<th>Тиск</th>
						<th>Висота</th>
						<th>Вологість</th>
					</tr>

				<?php print_db_table($result);?>
				</table>
			</div>
			<?php page_navigator($count, $page, $num_of_pages); ?>	<!-- Вивід навігатора сторінок БД -->
			<h2>Графіки</h2>
			<div class="chart-container">Ініціалізація графіків 
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
		<script src="js/utils.js"></script>							<!-- Підключення додаткових бібліотек -->
		<script src="js/chart.min.js"></script>
		<script src="js/jquery.js"></script>
	</body>
</html>


<!----------------------------------------------------------------------------------------->
<!-- Поле script -->
<!----------------------------------------------------------------------------------------->
<script>									
$(document).ready(function(){ 										// Скрипт для динамічного оновлення інформації 
	loadData();	
	loadDB();								
});
var loadData = function() {
	$.ajax({														// ajax-запит до бази даних 
		type:"GET",
		url:"/extract.php",											// звертання до файла extract.php 
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




function printRow(object) {
    var $line = $("<tr></tr>");
    for (const key in object)
        if (Object.hasOwnProperty.call(object, key))
            $line.append($("<td></td>").html(object[key]));
    return $line;
}
function fillInTable (data) {
    var $table = $("<table></table>");
    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        var $line = $("<tr></tr>");
        console.log();
        $table.append(printRow(element));
    }
    // $table.appendTo($("#dbTable"));
}

// fillInTable(db);


function printRow(data) {
	var row = "";
	row += "<tr>";
	for (var element in data)
		row += "<th>"+element+"</th>";
	row += "</tr>";
	return row;
}

var loadDB = function() {
	$.ajax({														// ajax-запит до бази даних 
		type:"GET",
		url:"/extract_db.php",										// звертання до файла extract_db.php 
		dataType: "json",
		success: function(result){
			var html = "";
			var dbTableHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
			html+=printRow(dbTableHeader);
            for (let index = 0; index < db.length; index++) {
                console.log(row);
				html+=printRow(db[index]);
    	    }

			document.getElementById("dbTable").innerHTML = html;
		}
	});
};	

										// виклик функції з scripts.php відображення графіків




</script>

