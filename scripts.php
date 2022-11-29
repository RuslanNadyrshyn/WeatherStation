<?php 
include ".env.php";
?>
<script>
    const APPID = "<?php echo '$APPID';?>";

    var loadData = function () {
        $.ajax({													// ajax-запит до бази даних для динамічного 
            type: "GET",                                            // виводу даних в таблицю "Дані датчика BME280".
            url: "/extract.php",									// Виклик файла extract.php, в якому виконується запит до БД
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
        $.ajax({													// ajax-запит до бази даних для динамічного 
            type: "GET",                                            // виводу даних в таблицю "Дані датчика BME280".
            url: "https://api.openweathermap.org/data/2.5/weather?q=" + city + "&appid=" + APPID + "&units=metric&lang=ua",
            dataType: "json",
            success: function (result) {
                weatherData = [
                    {
                        description: result.weather.description
                    },
                    {
                        temp: result.main.temp
                    },
                    {
                        pressure: result.main.pressure
                    },
                    {
                        humidity: result.main.humidity
                    },
                    {
                        clouds: result.clouds.all
                    },
                    {
                        wind: result.wind.speed
                    },
                ]
                console.log(result);
                var weatherHeader = ["Погода", "Температура", "Тиск", "Вологість", "Хмарність", "Вітер"]; 
                var $table = createTable(weatherData, weatherHeader);              // виклик ф-ції createTable() з відповідними даними
                $("#weatherTable").empty();

                $table.appendTo($("#weatherTable"));
            }
        });
    };

    /*
    { 
        "coord": { 
            "lon": 24.0232, 
            "lat": 49.8383 
        }, 
        "weather": [{ 
            "id": 801, 
            "main": "Clouds", 
            "description": "кілька хмар", 
            "icon": "02d" 
        }], 
        "base": "stations", 
        "main": { 
            "temp": 2.11, 
            "feels_like": -1.89, 
            "temp_min": 2.11, 
            "temp_max": 2.11, 
            "pressure": 1026,
            "humidity": 73, 
            "sea_level": 1026, 
            "grnd_level": 990 
        }, 
        "visibility": 10000, 
        "wind": { 
            "speed": 4.32, 
            "deg": 123, 
            "gust": 6.28 
        }, 
        "clouds": { 
            "all": 12 
        }, 
        "dt": 1669719089, 
        "sys": { 
            "country": "UA", 
            "sunrise": 1669701429, 
            "sunset": 1669732052 
        },
        "timezone": 7200, 
        "id": 702550, 
        "name": "Lviv", 
        "cod": 200 
    }

    https://api.openweathermap.org/data/2.5/weather?q=Lviv&appid=&units=metric&lang=ua
    
    */

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

    function createTable(data, header) {                            // Функція для створення таблиці, яка приймає
            var $table = $("<table cellspacing='0'></table>");      // масив даних таблиці(data) та головний рядок(header)
            var $thead = $("<thead></thead>");
            var $tbody = $("<tbody></tbody>");

            $thead.append(printRow(header, true));
            $table.append($thead);

            for (let index = 0; index < data.length; index++) {
                var element = data[index];
                var $line = $("<tr></tr>");
                $tbody.append(printRow(element, false));
            }
            $table.append($tbody);
            return $table;
    }

    function printWeather(data) {                                        // Функція для створення таблиці "База даних"
        var dbHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
        var $table = createTable(data, dbHeader);                   // виклик ф-ції createTable() з відповідними даними
        $("#dbTable").empty();
        $table.appendTo($("#dbTable"));
    }

    function printDB(data) {                                        // Функція для створення таблиці "База даних"
        var dbHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
        var $table = createTable(data, dbHeader);                   // виклик ф-ції createTable() з відповідними даними
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

</script>