/* ------------------------------- Weather -------------------------------*/

function changeLocation(newLocation) {
    $("#location").html(newLocation);
    localStorage.setItem("city", newLocation);

    getWeather(newLocation);						                // Виклик функції для створення таблиці з даними погоди
}

/* ------------------------------- Navigator -------------------------------*/

function printNavCounter(count) {
    var counterArray = [20, 50, 100, 200, 500, 1000];

    var $counterList = $("<nav class=\"navigator-block\"></nav>");

    for (let i = 0; i < counterArray.length; i++) {
        const element = counterArray[i];
        var $navItem = createNavItem("count", element, count, "selected");
        $counterList.append($navItem);
    }

    $("#navCounter").empty();
    $counterList.appendTo($("#navCounter"));
}

function printNavPages(count, page) {
    if (count < 0) return;
    var numOfPages = getNumOfPages(count);
    localStorage.setItem("numOfPages", numOfPages);

    $("#page").text(page);
    $("#numOfPages").text(numOfPages);

    if (page > numOfPages) {
        localStorage.removeItem("page");
        return false;
    }

    var $pages = $("<nav class=\'navigator-block pages\'></nav>");

    for (let i = 1; i <= numOfPages; i++) {
        var $navItem = createNavItem("page", i, page, "selected");
        $pages.append($navItem);
    }

    $("#navPages").empty();
    $pages.appendTo($("#navPages"));
    return true;
}

function createNavItem(item, element, selected, itemClass) {
    var $navItem = $("<a class=\"navigator-item\"></a>");

    if (element == selected) $navItem.addClass(itemClass);
    $navItem.append(element);
    $navItem.click(function () {
        localStorage.setItem(item, element);
        updateTable();
    });
    return $navItem;
}

function printOrderList(name, options, param) {
    var $list = createSelectList(name, options, param);

    $("#selectOrder").empty();
    $("#selectOrder").replaceWith($list);
}

function printParamList(name, options, param) {
    var $list = createSelectList(name, options, param);

    $("#selectParam").empty();
    $("#selectParam").replaceWith($list);
}

function createSelectList(name, options, param) {
    var $list = $("<select></select>");
    $list.addClass("select");
    $list.attr('name', name);
    $list.attr('id', name);
    $list.val(param);

    $list.change(function () {
        var value = $(this).find('option:selected').attr('val');
        localStorage.setItem(name, value);
        updateTable();
    });

    for (let i = 0; i < options.length; i++) {
        const element = options[i];
        var $option = $("<option></option>");
        $option.attr('val', element.value);
        $option.html(element.text);

        if (element.value == param)
            $option.attr("selected", "selected");

        $list.append($option);
    }
    return $list;
}

function changeValue(itemName, value) {
    localStorage.setItem(itemName, value);
    updateTable();
}

/* -------------------------------- Database --------------------------------*/

function updateTable() {
    var page = localStorage.getItem("page") != null ?
        Number(localStorage.getItem("page")) : 1;
    var count = localStorage.getItem("count") != null ?
        Number(localStorage.getItem("count")) : 20;
    var param = localStorage.getItem("param") != null ?
        localStorage.getItem("param") : "date";
    var order = localStorage.getItem("order") != null ?
        localStorage.getItem("order") : "DESC";


    var options = [
        { value: "id", text: "ID" },
        { value: "date", text: "Час" },
        { value: "temp", text: "Температура" },
        { value: "press", text: "Тиск" },
        { value: "alt", text: "Висота" },
        { value: "hum", text: "Вологість" }
    ];
    var orders = [
        { value: "DESC", text: "По спаданню" },
        { value: "ASC", text: "По зростанню" }
    ]

    printParamList("param", options, param);
    printOrderList("order", orders, order);

    printNavCounter(count);

    if (printNavPages(count, page)) {     // оновляти таблицю, якщо кількість сторінок не менше обраної
        fetchDB(page, count, param, order);
    }
    else                                // Якщо кількість сторінок менше обраної,
        updateTable();                  // видалити з пам'яті номер сторінки та перезапустити функцію
}


function createTable(data, header) {                // Функція для створення таблиці, яка приймає               
    var $table = $("<table cellspacing='0'></table>");          // масив даних таблиці(data) та головний рядок(header)
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

function printRow(object, isHeader) {                           // Допоміжна функція для створення рядка таблиці
    var $line = $("<tr></tr>");
    var param = localStorage.getItem("param") != null ?
        localStorage.getItem("param") : "DESC";

    if (isHeader) object.forEach(element =>
        $line.append($("<th class='sticky'></th>").html(element)));
    else {
        for (const key in object) {
            if (Object.hasOwnProperty.call(object, key)) {
                var $td = $("<td></td>");

                if (key == param + "_bme280")
                    $td.addClass("sorted");

                if (key == "date_bme280") {
                    datetime = object[key].split(" ");
                    date = datetime[0];
                    time = datetime[1];

                    $line.append($td.clone().append(date));
                    $line.append($td.clone().append(time));
                } else $line.append($td.append(object[key]));
            }
        }
    }
    return $line;
}

/* --------------------------------- Charts ---------------------------------*/

function drawCharts(res, labels) {                          // Ф-ція створення графіків
    var order = localStorage.getItem("order") != null ?
        localStorage.getItem("order") : "DESC";

    if (order == "DESC")
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

function toggleChart(id) {                                      // Функція збільшення графіка при натисканні
    var element = document.getElementById(id.id);
    element.classList.toggle("large");
}
