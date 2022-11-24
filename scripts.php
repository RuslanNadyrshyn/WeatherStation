<?php
include "connect_db.php"; 

function print_db_table($result) {
	while($myrow=$result->fetch_array(MYSQLI_ASSOC)){						// Вивід усіх значень таблиці
		$php_date = date("d/m/Y", strtotime($myrow['date_bme280']));     	// Перевод дати у формат "d/m/Y"
		$php_time = date("H:i:s", strtotime($myrow['date_bme280']));     	// Перевод часу у формат "H:i:s"
		echo "<tr>";
		echo "<td>"$myrow['id_bme280']"</td>";
		echo "<td>"$php_date"</td>";
		echo "<td>"$php_time"</td>";
		echo "<td>"$myrow['temp_bme280']." &degС</td>";
		echo "<td>"$myrow['press_bme280']." гПа</td>";
		echo "<td>"$myrow['alt_bme280']." м</td>";
		echo "<td>"$myrow['hum_bme280']." %</td>";
		echo "</tr>";
	}
}
?>

<script>
// // Скрипт для динамічного оновлення інформації у таблиці "Дані датчика BME280" 
// $(document).ready(function(){
//     loadData();
// });
// var loadData = function() {
//     $.ajax({						//<!-- ajax-запит до бази даних -
//         type:"GET",
//         url:"/extract.php",			//<!-- звертання до файла extract.php -
//         dataType: "json",
//         success: function(result){
//             $("#temp").text(result.temp_bme280 + ' °С');
//             $("#press").text(result.press_bme280 + ' гПа');
//             $("#alt").text(result.alt_bme280 + ' м');
//             $("#hum").text(result.hum_bme280 + ' %');
//             setTimeout(loadData, 2000); 
//         },
//     });
// };

function createConfig(labels, data, text, colorName) {
	return {
		type: 'line',
		data: {
			labels: labels,
			datasets: [{
				label: text,print_db_table
				data: data,
				backgroundColor: colorName,
				borderColor: colorName,
				borderWidth: 1
				}]
		},
		options: {
			responsive: true
		}
	};
}

function drawCharts (data, labels) {
    console.log("data: ", data);
    console.log("labels: ", labels);
    window.onload = function() {
        [{
            id: 'chart-temp',	// Графік температури
            color: 'yellow',
            text: 'Температура',
            data: data[0]
        }, {
            id: 'chart-press',	// Графік тиску
            color: 'red',
            text: 'Тиск',
            data: data[1]
        }, {
            id: 'chart-alt', 	// Графік Висоти
            color: 'green',
            text: 'Висота над рівнем моря',
            data: data[2]
        }, {
            id: 'chart-hum', 	// Графік вологості
            color: 'blue',
            text: 'Вологість',
            data: data[3]
        }].forEach(function(details) {
            var ctx = document.getElementById(details.id).getContext('2d');
            var config = createConfig(labels, details.data, details.text, details.color);
            new Chart(ctx, config);
        });
    };
}

function fetchArray(array, value) {		// функція для розбиття строки на масив
	return [
			<?php 
				while($t=mysqli_fetch_array(array)){
					echo '"'.$t[value].'",';
				}
			?>
		].slice(0, -1);
}
</script>
