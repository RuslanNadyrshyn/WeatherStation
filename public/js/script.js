/* ------------------------------- Weather -------------------------------*/

function showDropdown() {
    $(".dropdown-content").toggle( function () {
        $(".dropdown-content").addClass("active");
    }, function () {
        $(".dropdown-content").removeClass("active");
    });
}

function changeLocation(newLocation) {
    $("#location").html(newLocation);
    localStorage.setItem("city", newLocation);
    getWeather(newLocation);						                // Виклик функції для створення таблиці з даними погоди
}

function showCities() {
    var $content = $("<nav class=\"dropdown-content\"></nav>");
    CITIES.forEach(city => {
        var $city = $("<a></a>");
        $city.text(city);
        $city.click(function () {
            changeLocation(city);
        });

        $content.append($city);
    });

    $("#cities").replaceWith($content);
}

/* ------------------------------- Navigator -------------------------------*/

function printNavCounter(count, page) {
    var $counterList = $("<nav class=\"navigator-block\"></nav>");

    for (let i = 0; i < COUNTER_LIST.length; i++) {
        const element = COUNTER_LIST[i];
        var $navItem = createNavItem("count", element, count);
        $counterList.append($navItem);
    }
    $("#navCounter").empty();
    $counterList.appendTo($("#navCounter"));

    var numOfPages = getNumOfPages(count);    
    changeValue("numOfPages", numOfPages);

    if (numOfPages < page) {
        localStorage.removeItem("page");
        printNavPages(numOfPages, 1); 
    }
    else printNavPages(numOfPages, page);       
}

function printNavPages(numOfPages, page) {
    $("#page").text(page);
    $("#numOfPages").text(numOfPages);

    var $pages = $("<nav class=\'navigator-block pages\'></nav>");

    for (let i = 1; i <= numOfPages; i++) {
        var $navItem = createNavItem("page", i, page);
        $pages.append($navItem);
    }

    $("#navPages").empty();
    $pages.appendTo($("#navPages"));
}

function createNavItem(item, element, selected) {
    var $navItem = $("<a class=\"navigator-item\"></a>");
    $navItem.append(element);

    if (element == selected) 
        $navItem.addClass("selected");

    $navItem.click(function () {
        if (item == "count") {
            $(".nav-counter .selected").attr('class', 'navigator-item');
            $(this).addClass("selected");

            var numOfPages = getNumOfPages($(this).text());
            var page = getLocalStorageItem("page", 1);

            if (numOfPages < page) {                // Якщо кількість сторінок менше обраної,
                localStorage.setItem("page", 1);    // видалити з пам'яті номер сторінки 
                printNavPages(numOfPages, 1);
            } else {
                localStorage.setItem("page", page);
                printNavPages(numOfPages, page);
            }
            changeValue("count", $(this).text());
        }
        else if (item == "page"){   
            $(".pages .selected").attr('class', 'navigator-item');

            $(this).addClass("selected");
            $("#page").text($(this).text());
            changeValue("page", $(this).text());
        }
    });
    return $navItem;
}

function printSelectList(name, options, param, id) {
    var $list = createSelectList(name, options, param);

    $(id).empty();
    $(id).replaceWith($list);
}

function createSelectList(name, options, param) {
    var $list = $("<select></select>");
    $list.addClass("select");
    $list.attr('name', name);
    $list.attr('id', name);
    $list.val(param);

    $list.change(function () {
        var value = $(this).find('option:selected').attr('val');
        changeValue(name, value);
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
    var items = getItems();
    fetchDB(items.page, items.count, items.param, items.order);          
}


function createTable(data, header) {                // Функція для створення таблиці, яка приймає               
    var $table = $("<table cellspacing='0'></table>");          // масив даних таблиці(data) та головний рядок(header)
    var $thead = $("<thead></thead>");
    var $tbody = $("<tbody></tbody>");

    $thead.append(printRow(header, true));
    $table.append($thead);

    for (let index = 0; index < data.length; index++) {
        var element = data[index];
        $tbody.append(printRow(element, false));
    }
    $table.append($tbody);
    return $table;
}

function printRow(object, isHeader) {                           // Допоміжна функція для створення рядка таблиці
    var $line = $("<tr></tr>");
    var param = getLocalStorageItem("param", "id");

    if (isHeader) object.forEach(element =>
        $line.append($("<th class='sticky'></th>").html(element)));
    else {
        for (const key in object) {
            if (Object.hasOwnProperty.call(object, key)) {
                var $td = $("<td></td>");

                if (key == param + "_bme280")
                    $td.addClass("sorted");

                if (key == "date_bme280") {
                    var date = object[key][0];
                    var time = object[key][1];

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
    var order = getLocalStorageItem("order", "DESC");

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
    var pointRadius;
    console.log(data.length);
    if (pointRadius >= 500) pointRadius = 0;
    else if (data.length >= 200) pointRadius = 1;
    else if (data.length >= 100) pointRadius = 2;
    else if (data.length >= 50) pointRadius = 3;
    else pointRadius = 5;
    return {
        type: 'line',
        data: {
            labels: labels,
            datasets: [{
                data: data,
                borderColor: colorName,
                borderWidth: 1,
                pointRadius: pointRadius
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
                x: {
                    ticks: {
                        maxRotation: 0,
                        minRotation: 0,
                        font: {
                            size: 11
                        }
                    }
                },
                y: {
                    offset: true
                },
            }
        }
    };
}

/* --------------------------------- Helpers ---------------------------------*/

function toggleChart(id) {                                      // Функція збільшення графіка при натисканні
    var element = document.getElementById(id.id);
    element.classList.toggle("large");
}

function printError(jqXHR, exception, dest) {
    var msg = '';
    if (jqXHR.status === 0) {
        msg = 'Not connect.\n Verify Network.';
    } else if (jqXHR.status == 404) {
        msg = 'Requested page not found. [404]';
    } else if (jqXHR.status == 500) {
        msg = 'Internal Server Error [500].';
    } else if (exception === 'parsererror') {
        msg = 'Requested JSON parse failed.';
    } else if (exception === 'timeout') {
        msg = 'Time out error.';
    } else if (exception === 'abort') {
        msg = 'Ajax request aborted.';
    } else {
        msg = 'Uncaught Error.\n' + jqXHR.responseText;
    }
    console.log("msg",msg);
    localStorage.setItem("ServerError", msg);
    $(dest).text(""+ msg);
}

function getItems() {
    var items = {
        page: getLocalStorageItem("page", 1),
        count: getLocalStorageItem("count", COUNTER_LIST[0]),            
        param: getLocalStorageItem("param", OPTIONS[0].value),
        order: getLocalStorageItem("order", "DESC"),
        city: getLocalStorageItem("city", "Київ") 			// якщо міста немає в пам'яті, використовувати значення "Київ"	
    };

    return items;
}

function getLocalStorageItem(name, defaultValue) {
    var item = localStorage.getItem(name);
    if(item == null) {
        localStorage.setItem(name, defaultValue);
        return defaultValue;
    }
    return item;
}