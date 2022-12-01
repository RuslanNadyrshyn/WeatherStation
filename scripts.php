<?php
include "env/.env.php";
?>
<script>
    const APPID = "<?php echo $APPID; ?>";
    const WEATHER_SOURCE = "http://openweathermap.org/img/wn/";
    const PNG_ENDING = "@2x.png";
    
    /* ------------------------------- Weather -------------------------------*/

    var loadWeather = function (city) {
        $.ajax({													    // ajax-запит до бази даних для динамічного 
            type: "GET",                                                // виводу даних в таблицю "Дані датчика BME280".
            url: "https://api.openweathermap.org/data/2.5/weather?q=" + city + "&appid=" + APPID + "&units=metric&lang=ua",
            dataType: "json",
            success: function (result) {
                weatherData = [
                    { description: result.weather[0].description },
                    { temp: result.main.temp + " °С" },
                    { pressure: result.main.pressure + " ГПа" },
                    { humidity: result.main.humidity + " %" },
                    { clouds: result.clouds.all + " %" },
                    { wind: result.wind.speed + " м/с" }
                ]
                console.log(result);

                document.getElementById("location-weather").innerHTML = result.main.temp + " °С";
                document.getElementById("weather-icon").src =
                    WEATHER_SOURCE + result.weather[0].icon + PNG_ENDING;
                document.getElementById("weather-content-icon").src =
                    WEATHER_SOURCE + result.weather[0].icon + PNG_ENDING;

                var weatherHeader = ["Погода", "Температура", "Тиск", "Вологість", "Хмарність", "Вітер"];
                var $table = createTable(weatherData, weatherHeader, true); // виклик ф-ції createTable() з відповідними даними

                $("#weatherTable").empty();
                $table.appendTo($("#weatherTable"));
            }
        });
    };

    function changeLocation(newLocation) {
        document.getElementById("location").innerHTML = newLocation;
        loadWeather(newLocation);						            // Виклик функції для створення таблиці з даними погоди
    }

    /* ------------------------------- Current_BME280 -------------------------------*/

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

    /* ------------------------------- Navigator -------------------------------*/

    function printNavCounter () {
        var counterArray = [20, 50, 100, 200, 500, 1000];
        var $counterList = createCounterList(counterArray);
        
        $("#navCounter").empty();
        $counterList.appendTo($("#navCounter"));
    }

    function printNavPages(count) {
        if (count < 0) return;
        var numOfPages = getNumOfPages(count);
        document.getElementById("numOfPages").innerText = numOfPages;
        document.getElementById("page").innerText = 1;

        var $pages = createNavList(numOfPages); 

        $("#navPages").empty();
        $pages.appendTo($("#navPages"));
    }

    function createCounterList(counterArray) {
        console.log("createCounterList");
        var $nav = $("<nav class=\"navigator-block\"></nav>");
        
        for (let i = 0; i < counterArray.length; i++) {
            const element = counterArray[i];

            var $counterItem = $("<a class=\"navigator-item\"></a>");
            
            if (i == 0) $counterItem.addClass("selected");

            $counterItem.append(element);
            $counterItem.attr('id', "counter" + element);
            $counterItem.click(function () {
                clickCounter(element);
            });
            $nav.append($counterItem);
        }
        return $nav;
    }

    function createNavList(numOfPages) {                // Функція для створення таблиці, яка приймає               
        var $nav = $("<nav class=\'navigator-block pages\'></nav>");          // масив даних таблиці(data) та головний рядок(header)

        for (let i = 1; i <= numOfPages; i++) {
            var $navItem = $("<a class=\"navigator-item\"></a>");

            if (i == 1) $navItem.addClass("selected-page");

            $navItem.append(i);
            $navItem.attr('id', "page" + i);
            $navItem.click(function () {
                clickPage(i);
            });
            $nav.append($navItem);
        }
        return $nav;
    }

    function clickCounter(element) {
        var elements = document.getElementsByClassName('selected');
        for (let j = 0; j < elements.length; j++)
            elements[j].classList.toggle("selected");

        document.getElementById("counter" + element).classList.toggle("selected");
        document.getElementById("counter").value = element;

        updateTable();
    }

    function clickPage(i) {
        var elements = document.getElementsByClassName('selected-page');
        for (let j = 0; j < elements.length; j++)
            elements[j].classList.toggle("selected-page");

        var element = document.getElementById("page" + i).classList.toggle("selected-page");

        document.getElementById("page").innerText = i;

        updateTable();
    }

    var getNumOfPages = function (count) {
        var numOfPages = 0;
        $.ajax({
            async: false,											// ajax-запит до бази даних для динамічного 
            type: "GET",                                            // виводу даних в таблицю "Дані датчика BME280".
            url: "database/get_num_of_rows.php?count=" + count,		// Виклик файла extract.php, в якому виконується запит до БД
            dataType: "json",
            success: function (result) {
                var rows = Number(result.rows);
                numOfPages = Math.ceil(rows / count);
            }
        });
        return numOfPages;
    };

    /* -------------------------------- Database --------------------------------*/

    function updateTable() {
        var page = document.getElementById("page").innerText;
        var counter = document.getElementById("counter").value;
        console.log("counter:", counter);
        // var param = document.getElementById("param").value;
        var order = document.getElementById("order").value;
        console.log("order:", order);
        loadTable(page, counter, "date", order);
    }
    
    function loadTable(page, count, param, order) {
        var _page = "1";
        var _count = "20";
        var _param = "date";
        var _order = "DESC";

        if (page) _page = page;
        if (count) _count = count;
        if (param) _param = param;
        if (order) _order = order;

        $.ajax({
            type: "GET",
            url: "database/fetch_db.php?" + "page=" + _page + "&count=" + _count + "&param=" + _param + "&order=" + _order,
            dataType: "json",
            success: function (result) {
                printDB(result);
                printCharts(result);
            }
        });
    };

    function printDB(data) {                                        // Функція для створення таблиці "База даних"
        var dbHeader = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
        var $table = createTable(data, dbHeader, false);            // виклик ф-ції createTable() з відповідними даними
        $("#dbTable").empty();
        $table.appendTo($("#dbTable"));
    }

    function createTable(data, header, isVertical) {                // Функція для створення таблиці, яка приймає               
        var $table = $("<table cellspacing='0'></table>");          // масив даних таблиці(data) та головний рядок(header)
        var $thead = $("<thead></thead>");
        var $tbody = $("<tbody></tbody>");

        if (isVertical) {
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


    /* --------------------------------- Charts ---------------------------------*/

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

    function drawCharts(res, labels) {                              // Ф-ція створення графіків
        for (const key in res)
            if (Object.hasOwnProperty.call(res, key))
                res[key].reverse();
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
            let chartStatus = Chart.getChart(details.id);
            if (chartStatus != undefined) chartStatus.destroy();

            var ctx = document.getElementById(details.id).getContext('2d');
            var config = createConfig(labels, details.data, details.color);
            new Chart(ctx, config);
        });
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



    function toggleChart(id) {                                      // Функція збільшення графіка при натисканні
        var element = document.getElementById(id.id);
        element.classList.toggle("large");
    }
</script>