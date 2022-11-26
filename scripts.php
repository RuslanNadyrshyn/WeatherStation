<script>
    function printRow(object, isHeader) {
        var $line = $("<tr></tr>");
        if (isHeader) object.forEach(element =>
            $line.append($("<th></th>").html(element)));
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
    function fillInTable(data) {
        var $table = $("<table cellspacing='0' ></table>");
        $table.append(printRow(["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"], true));
        for (let index = 0; index < data.length; index++) {
            var element = data[index];
            var $line = $("<tr></tr>");
            $table.append(printRow(element), false);
        }
        return $table;
    }


    function createConfig(labels, data, text, colorName) {
        return {
            type: 'line',
            data: {
                labels: labels,
                datasets: [{
                    label: text,
                    data: data,
                    backgroundColor: colorName,
                    borderColor: colorName,
                    borderWidth: 1,
                }]
            },
            options: {
                scales: {
                    xAxis: {
                        reverse: true
                    }
                }
            }
        };
    }

    function drawCharts(data, labels) {
        console.log("data: ", data);
        console.log("labels: ", labels);
        window.onload = function () {
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
            }].forEach(function (details) {
                var ctx = document.getElementById(details.id).getContext('2d');
                var config = createConfig(labels, details.data, details.text, details.color);
                new Chart(ctx, config);
            });
        };
    }



</script>