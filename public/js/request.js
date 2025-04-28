var getCurrentData = function () {      // Функція, яка виконує ajax-запит до бази даних  
    ledSlider = getLocalStorageItem("ledSlider", 0);
    $.ajax({						    // за допомогою файла "get_current.php" для динамічного
        type: "GET",                    // виводу даних в таблицю "Дані датчика BME280".
        url: "/src/get_current.php?ledSlider=" + ledSlider,
        dataType: "json",
        success: function (result) {    // Заповнення отриманими даними відповідних елементів таблиці
            $("#time-current").text(result.time);
            $("#temp").text(result.temp_bme280 + ' °С');
            $("#press").text(result.press_bme280 + ' гПа');
            $("#hum").text(result.hum_bme280 + ' %');

            localStorage.setItem("maxCount", result.max_count);

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
        url: "/src/get_num_of_rows.php?count=" + count,
        dataType: "json",
        success: function (result) {
            totalRows = Number(result.num_of_rows);
            $("#recordCount").text("Всього записів в базі даних: " + totalRows);
            numOfPages = Math.ceil(totalRows / count);
        }, 
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, 'post');
        },
    });
    return numOfPages;
};

var getLedSliderValue = function () {  // Get slider value from database by calling /src/get_slider.php
    var sliderValue = 0;
    $.ajax({
        async: false,
        type: "GET",
        url: "/src/get_slider.php",
        dataType: "json",
        success: function (result) {
            sliderValue = Number(result.slider);
            document.getElementById("ledSlider").value = sliderValue;
        },
        error: function (jqXHR, exception) {
            printError(jqXHR, exception, 'post');
        },
    });
    return sliderValue;
};

