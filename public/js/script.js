/* ------------------------------- Weather -------------------------------*/

function changeLocation(newLocation) {
    $("#location").html(newLocation);
    localStorage.setItem("city", newLocation);

    getWeather(newLocation);						                // Виклик функції для створення таблиці з даними погоди
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
        
    if (numOfPages < page) {
        localStorage.removeItem("page");
        printNavPages(numOfPages, 1); 
    }
    
    printNavPages(numOfPages, page);            
}

function printNavPages(numOfPages, page) {
    console.log(numOfPages,"/" ,page);
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
    console.log("i:", element, "selected:", selected);
    var $navItem = $("<a class=\"navigator-item\"></a>");
    $navItem.append(element);

    if (element == selected) 
        $navItem.addClass("selected");

    $navItem.click(function () {
        if (item == "count") {
            $(".nav-counter .selected").attr('class', 'navigator-item');
            $navItem.addClass("selected");

            var numOfPages = getNumOfPages($navItem.text());
            page = localStorage.getItem("page");
            
            if (numOfPages < page) {                // Якщо кількість сторінок менше обраної,
                localStorage.setItem("page", 1);    // видалити з пам'яті номер сторінки 
                page = 1; 
            } else 
                localStorage.setItem("page", page);
               
            printNavPages(numOfPages, page);
            updateTable();
        }
        else if (item == "page"){   
            $(".pages .selected").attr('class', 'navigator-item');
            $(this).addClass("selected");
        
            changeValue("page", $navItem.text());
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

function setDefaults() {
    localStorage.setItem("ServerError", "");
    if (localStorage.getItem("count") == null)
        localStorage.setItem("count", COUNTER_LIST[0]);
    if (localStorage.getItem("param") == null)
        localStorage.setItem("param", DEFAULT_PARAM);
    if (localStorage.getItem("order") == null)
        localStorage.setItem("order", DEFAULT_ORDER);
    if (localStorage.getItem("city") == null)
        localStorage.setItem("city", DEFAULT_CITY);
    if (localStorage.getItem("page") == null || 
        localStorage.getItem("page") > localStorage.getItem("count"))
         localStorage.setItem("page", DEFAULT_PAGE);
}

function getItems() {
    var items = {
        page: localStorage.getItem("page"),
        count: localStorage.getItem("count"),       
        param: localStorage.getItem("param"),
        order: localStorage.getItem("order"),
        city: localStorage.getItem("city")	    	
    };

    return items;
}