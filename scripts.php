<?php 
include "env/.env.php";
?>
<script>
    const APPID = "<?php echo $APPID;?>";
    const WEATHER_SOURCE = "http://openweathermap.org/img/wn/";
    const PNG_ENDING = "@2x.png";

    var loadData = function () {
        $.ajax({													// ajax-запит до бази даних для динамічного 
            type: "GET",                                            // виводу даних в таблицю "Дані датчика BME280".
            url: "database/extract.php",									// Виклик файла extract.php, в якому виконується запит до БД
            dataType: "json",
            success: function (result) {
                
                $("#temp").text(result.temp_bme280 + ' °С');
                $("#press").text(result.press_bme280 + ' гПа');
                $("#alt").text(result.alt_bme280 + ' м');
                $("#hum").text(result.hum_bme280 + ' %');
                setTimeout(loadData, 2000);                         // Рекурсійний виклик функції для оновлення інформації кожні 2 секунди
            }
        });
    };

var loadWeather = function (city) {
    $.ajax({													    // ajax-запит до бази даних для динамічного 
        type: "GET",                                                // виводу даних в таблицю "Дані датчика BME280".
        url: "https://api.openweathermap.org/data/2.5/weather?q=" + city + "&appid=" + APPID + "&units=metric&lang=ua",
        dataType: "json",
        success: function (result) {
            weatherData = [
                { description: result.weather[0].description },
                { temp: result.main.temp+" °С" },
                { pressure: result.main.pressure+" ГПа" },
                { humidity: result.main.humidity+" %" },
                { clouds: result.clouds.all+" %" },
                { wind: result.wind.speed+" м/с" }
            ]
            console.log(result);

            document.getElementById("location-weather").innerHTML = result.main.temp+" °С";
            document.getElementById("weather-icon").src = 
                    WEATHER_SOURCE+result.weather[0].icon+PNG_ENDING;
            document.getElementById("weather-content-icon").src = 
                    WEATHER_SOURCE+result.weather[0].icon+PNG_ENDING;
            
            var weatherHeader = ["Погода", "Температура", "Тиск", "Вологість", "Хмарність", "Вітер"]; 
            var $table = createTable(weatherData, weatherHeader, true); // виклик ф-ції createTable() з відповідними даними
            
            $("#weatherTable").empty();
            $table.appendTo($("#weatherTable"));
        }
    });
};

    function printRow(object, isHeader) {                           // Допоміжна функція для створення рядка таблиці
        var $line = $("<tr></tr>");
        if (isHeader) object.forEach(element =>
            $line.append($("<th class='sticky'></th>").html(element)));
        else {
            for (const key in object)
                if (Object.hasOwnProperty.call(object, key)) {
                    if (key == "date_bme280") {
                        datetime = object[key].split(" ");
                        date = datetime[0];
                        time = datetime[1];
                        $line.append($("<td></td>").html(date));
                        $line.append($("<td></td>").html(time));
                    } else $line.append($("<td></td>").html(object[key]));
                }

        }
        return $line;
    }

    function createTable(data, header, isVertical) {                // Функція для створення таблиці, яка приймає               
        var $table = $("<table cellspacing='0'></table>");          // масив даних таблиці(data) та головний рядок(header)
        var $thead = $("<thead></thead>");
        var $tbody = $("<tbody></tbody>");

        if(isVertical) {
            for (let index = 0; index < header.length; index++) {
                var element = header[index];

                var $head = $("<th></th>");
                var $line = $("<tr></tr>");

                $head.append(element);
                $line.append($head);
                $thead.append($line);
            }
        } else $thead.append(printRow(header, true));
            
        $table.append($thead);

        for (let index = 0; index < data.length; index++) {
            var element = data[index];
            var $line = $("<tr></tr>");
            $tbody.append(printRow(element, false));
        }
        $table.append($tbody);
        return $table;
    }

    function printDB(data) {                                        // Функція для створення таблиці "База даних"
        var dbHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
        var $table = createTable(data, dbHeader, false);            // виклик ф-ції createTable() з відповідними даними
        $("#dbTable").empty();
        $table.appendTo($("#dbTable"));
    }

    function printCharts(data) {                                    // Функція для створення графіків
        var res = fetchResult(data);
        drawCharts(res, res.date);
    }

    function fetchResult(result) {                                  // Допоміжна функція для відокремлення окремих 
        var temp = [];                                              // показників від загальних даних, отриманих в БД
        var press = [];
        var alt = [];
        var hum = [];
        var date = [];

        result.forEach(element => {
            temp.push(element.temp_bme280);
            press.push(element.press_bme280);
            alt.push(element.alt_bme280);
            hum.push(element.hum_bme280);
            date.push(element.date_bme280);
        });

        return { temp, press, alt, hum, date };
    }

    function createConfig(labels, data, colorName) {                // допоміжна ф-ція для налаштування виводу графіків
        return {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    data: data,
                    borderColor: colorName,
                    borderWidth: 1,
                }]
            },
            options: {
                responsive: true,
                plugins: {
                    legend: {
                        display: false,
                    }
                },
                scales: {
                    y: {
                        offset: true
                    }
                }
            }
        };
    }

    function drawCharts(res, labels) {                              // Ф-ція створення графіків
        for (const key in res) {
            if (Object.hasOwnProperty.call(res, key)) {
                res[key].reverse();
            }
        }
        window.onload = function () {
            [{
                id: 'chart-temp',	                                // Графік температури
                color: 'yellow',
                data: res.temp,
            }, {
                id: 'chart-press',	                                // Графік тиску
                color: 'red',
                data: res.press,
            }, {
                id: 'chart-alt', 	                                // Графік висоти
                color: 'green',
                data: res.alt,
            }, {
                id: 'chart-hum', 	                                // Графік вологості
                color: 'blue',
                data: res.hum,
            }].forEach(function (details) {
                var ctx = document.getElementById(details.id).getContext('2d');
                var config = createConfig(labels, details.data, details.color);
                new Chart(ctx, config);
            });
        };
    }

    function toggleChart(id) {                                      // Функція збільшення графіка при натисканні
        var element = document.getElementById(id.id);
        element.classList.toggle("large");
    }

    function changeLocation(newLocation) {
		document.getElementById("location").innerHTML = newLocation;
		loadWeather(newLocation);						            // Виклик функції для створення таблиці з даними погоди
	}
</script>
