<script>
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

    function printRow(object, isHeader) {
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

    function createTable(data, header) {
        var $table = $("<table cellspacing='0'></table>");
        $table.append(printRow(header, true));
        for (let index = 0; index < data.length; index++) {
            var element = data[index];
            var $line = $("<tr></tr>");
            $table.append(printRow(element), false);
        }
        return $table;
    }

    function printWeather(data) {
        var weatherHeader = ["", "Температура", "Вологість"];
        var $table = createTable(data, weatherHeader);
        $("#weatherTable").empty();
        $table.appendTo($("#weatherTable"));
    }

    function printDB(data) {
        var dbHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
        var $table = createTable(data, dbHeader);
        $("#dbTable").empty();
        $table.appendTo($("#dbTable"));
    }

    function printCharts(data) {
        var res = fetchResult(data);
        drawCharts(res, res.date);
    }

    function fetchResult(result) {
        var temp = [];
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

        return {temp, press, alt, hum, date};
    }

    function createConfig(labels, data, colorName, scopes) {
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

    function drawCharts(res, labels) {
        for (const key in res) {
            if (Object.hasOwnProperty.call(res, key)) {
                res[key].reverse();
            }
        }
        window.onload = function () {
            [{
                id: 'chart-temp',	// Графік температури
                color: 'yellow',
                data: res.temp,
            }, {
                id: 'chart-press',	// Графік тиску
                color: 'red',
                data: res.press,
            }, {
                id: 'chart-alt', 	// Графік Висоти
                color: 'green',
                data: res.alt,
            }, {
                id: 'chart-hum', 	// Графік вологості
                color: 'blue',
                data: res.hum,
            }].forEach(function (details) {
                var ctx = document.getElementById(details.id).getContext('2d');
                var config = createConfig(labels, details.data, details.color);
                new Chart(ctx, config);
            });
        };
    }

    function toggleChart(id) {
        var element = document.getElementById(id.id);
        element.classList.toggle("large");
    }

</script>