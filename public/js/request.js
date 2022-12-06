const DB_HEADER = ["ID", "Дата", "Час", "Температура", "Тиск", "Висота", "Вологість"];
/*
    Дані отримуються за допомогою ajax-запиту до файла get_weather.php, який
виконує запит необхідними з даними до API.openweathermap.org
Після отримання даних, за допомогою jQuery заповнюються відповідні елементи HTML-коду. 

(тут картинка как єто віглядит)
*/
var getWeather = function (city) {      // Функція, яка виконує запит до API для отримання
    $.ajax({							// даних погоди обраного міста
        type: "GET",
        url: HOST + "/src/get_weather.php?city=" + city,
        dataType: "json",
        success: function (result) {    // Заповнення елементів таблиці даних погоди обраного міста
            console.log(result);
            var $img = $("<img class='weather-content-icon' src=''/>");
            $img.attr('src', WEATHER_SOURCE + result.weather[0].icon + PNG_ENDING);
            $("#weather").empty();
            $("#weather").text(result.weather[0].description);
            $("#weather").append($img);
            $("#temp-weather").text(result.main.temp + " °С");
            $("#temp-feels-weather").text(result.main.feels_like + " °С");
            $("#press-weather").text(result.main.pressure + " ГПа");
            $("#hum-weather").text(result.main.humidity + " %");
            $("#cloud-weather").text(result.clouds.all + " %");
            $("#wind-weather").text(result.wind.speed + " м/с");
            $("#location-weather").html(result.main.temp + " °С");
            $("#weather-icon").attr('src', WEATHER_SOURCE + result.weather[0].icon + PNG_ENDING);
        },
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, '#weather');
        },
    });
};

function setMaxCount (maxCount) {
    $.ajax({							// 
        type: "GET",
        url: HOST + "/src/set_maxcount.php?max_count=" + maxCount,
        dataType: "json",
        success: function (result) {    // 
            console.log("maxcount changed to", maxCount);
            $("#max-count").text("Запис в БД кожні " + maxCount + " значень");
        },
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, '#post');
        },
    });
}

var getCurrentData = function () {      // Функція, яка виконує ajax-запит до бази даних  
    call = $.ajax({						// за допомогою файла "get_current.php" для динамічного
        type: "GET",                    // виводу даних в таблицю "Дані датчика BME280".
        url: HOST + "/src/get_current.php",
        dataType: "json",
        success: function (result) {    // Заповнення отриманими даними відповідних елементів таблиці 
            console.log(result);
            $("#time-current").text("Останнє оновлення " + result.time);
            $("#temp").text(result.temp_bme280 + ' °С');
            $("#press").text(result.press_bme280 + ' гПа');
            $("#alt").text(result.alt_bme280 + ' м');
            $("#hum").text(result.hum_bme280 + ' %');
            $("#max-count").text("Запис до бази даних кожні " + result.max_count + " значень");
            setTimeout(getCurrentData, 5000); // Рекурсійний виклик функції для оновлення інформації кожні 2 секунди
        },
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, '#post');
        },
    });
};

var getNumOfPages = function (count) {  // Функція, яка за допомогою ajax-запиту до файла 
    var numOfPages = 0;                 // "get_num_of_rows.php" отримує кількість рядків та  
    $.ajax({                            // повертає кількість сторінок для обраної кількості рядків
        async: false,
        type: "GET",
        url: HOST + "/src/get_num_of_rows.php?count=" + count,
        dataType: "json",
        success: function (result) {
            var rows = Number(result.num_of_rows);
            numOfPages = Math.ceil(rows / count);
        }, 
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, 'post');
        },
    });
    return numOfPages;
};

/*
Функція, яка за допомогою ajax-запиту до файла "src/fetch_db.php" отримує дані таблиці
в обраних користувачем межах та за відповідними умовами, після чого викликає функції створення з отриманими даними
таблиці бази даних та графіків
*/
function fetchDB(page, count, param, order) {       // Функція, яка за допомогою ajax-запиту до файла
    $.ajax({                                        // "src/fetch_db.php" отримує дані таблиці
        type: "GET",                                // в обраних межах та за відповідними умовами, після чого викликає функції створення таблиці бази даних та графіків
        url: HOST + "/src/fetch_db.php?" + "page=" + page + "&count=" + count + "&param=" + param + "&order=" + order,
        dataType: "json",
        success: function (result) {
            for (let index = 0; index < result.length; index++) {
                var object = result[index];

                for (const key in object) {
                    if (Object.hasOwnProperty.call(object, key)) {
                        if (key == "date_bme280") 
                            object[key] = object[key].split(" ");
                    }
                }
                result[index] = object;
            }
            
            var $table = createTable(result, DB_HEADER); // Функція для створення таблиці "База даних"
            $("#dbTable").empty();
            $table.appendTo($("#dbTable"));

            var res = fetchResult(result);
            drawCharts(res, res.date);
        },
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, '#post');
        },
    });
};


