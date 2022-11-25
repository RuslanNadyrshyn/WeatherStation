<script>
// Скрипт для динамічного оновлення інформації у таблиці "Дані датчика BME280" 
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

</script>
