const rowsPerPageSelect = document.getElementById('rowsPerPage');
const tbody = document.getElementById('data-table-body');
const pagination = document.getElementById('pagination');
const recordCount = document.getElementById('recordCount');
const toggleButton = document.getElementById('toggle-history'); //
const historySection = document.getElementById('history-section');
const tableWrapper = document.querySelector('.table-wrapper');
const headers = document.querySelectorAll('thead th');

let currentPage = 1;
let totalRows = 0;
let rowsPerPage = parseInt(rowsPerPageSelect.value);
let currentSort = { column: 'date', order: 'asc' };
let isExpanded = false;

rowsPerPageSelect.addEventListener('change', () => {
    rowsPerPage = parseInt(rowsPerPageSelect.value);
    currentPage = 1;
    renderTable();
  });

toggleButton.addEventListener('click', () => {
    isExpanded = !isExpanded;
    if (isExpanded) {
        historySection.classList.add('expanded');
        toggleButton.textContent = "Сховати історію 🔼";
        renderTable();
        //   createChart();
    } else {
        historySection.classList.remove('expanded');
        toggleButton.textContent = "Показати історію 🔽";
    }
});

// Клік по заголовку таблиці для сортування
headers.forEach(header => {
    header.addEventListener('click', () => {
      const column = header.dataset.column;
      if (currentSort.column === column) {
        currentSort.order = currentSort.order === 'asc' ? 'desc' : 'asc';
      } else {
        currentSort.column = column;
        currentSort.order = 'asc';
      }
      currentPage = 1;
      renderTable();
    });
  });

function renderTable() {
    $.ajax({
        type: "GET",
        url: "/src/fetch_db.php?" + "page=" + currentPage + "&count=" + rowsPerPage + "&param=" + currentSort.column + "&order=" + currentSort.order,
        dataType: "json",
        success: function (result) {
            for (let index = 0; index < result.length; index++) {
                var object = result[index];

                for (const key in object) {
                    if (Object.hasOwnProperty.call(object, key)) {
                        if (key == "date_bme280")           // Розділення строки на дату та час
                            object[key] = object[key].split(" ");
                    }
                }
                result[index] = object;
            }

            // var res = fetchResult(result);                  // Вибірка з результатів даних для кожного графіка
            // drawCharts(res, res.date);                      // Функція заповнення графіків

            tbody.innerHTML = '';

            headers.forEach(h => h.classList.remove('sorted'));

            let sortedData = result;
            if (currentSort.column) {
                const sortedHeader = Array.from(headers).find(h => h.dataset.column === currentSort.column);

                if (sortedHeader) {
                    sortedHeader.classList.add('sorted');
                }
            }
            
            sortedData.forEach(entry => {
                const row = `<tr>
                    <td>${entry.date_bme280}</td>
                    <td>${entry.temp_bme280}</td>
                    <td>${entry.hum_bme280}</td>
                    <td>${entry.press_bme280}</td>
                </tr>`;
                tbody.insertAdjacentHTML('beforeend', row);
            });

            renderPagination(sortedData.length);

            tableWrapper.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        },
        error: function (jqXHR, exception) {                // Повідомлення у випадку помилки
            printError(jqXHR, exception, '#post');
        },
    });
}

function renderPagination(totalItems) {
    pagination.innerHTML = '';

    numOfPages = getNumOfPages(rowsPerPage);

    const pageCount = numOfPages;

    const prevButton = document.createElement('button');
    prevButton.innerText = '← Назад';
    prevButton.disabled = currentPage === 1;
    prevButton.addEventListener('click', () => {
        if (currentPage > 1) {
            currentPage--;
            renderTable();
        }
    });
    pagination.appendChild(prevButton);

    if (currentPage > 3) {
        addPageButton(1);
        addEllipsis();
    }

    for (let i = Math.max(1, currentPage - 2); i <= Math.min(pageCount, currentPage + 2); i++) {
        addPageButton(i);
    }

    if (currentPage + 2 < pageCount) {
        addEllipsis();
        addPageButton(pageCount);
    }

    const nextButton = document.createElement('button');
    nextButton.innerText = 'Вперед →';
    nextButton.disabled = currentPage === pageCount;
    nextButton.addEventListener('click', () => {
        if (currentPage < pageCount) {
            currentPage++;
            renderTable();
        }
    });
    pagination.appendChild(nextButton);

    function addPageButton(page) {
        const btn = document.createElement('button');
        btn.innerText = page;
        if (page === currentPage) btn.classList.add('active');
        btn.addEventListener('click', () => {
            currentPage = page;
            renderTable();
        });
        pagination.appendChild(btn);
    }

    function addEllipsis() {
        const span = document.createElement('span');
        span.innerText = '...';
        pagination.appendChild(span);
    }
}



/* --------------------------------- Charts ---------------------------------*/

function drawCharts(res, labels) {                                  // Ф-ція створення графіків
    var order = getLocalStorageItem("order", "DESC");

    if (order == "DESC")
        for (const key in res)
            if (Object.hasOwnProperty.call(res, key))
                res[key].reverse();
    [{
        id: 'chart-temp',	                                        // Графік температури
        color: 'yellow',
        data: res.temp,
    }, {
        id: 'chart-press',	                                        // Графік тиску
        color: 'red',
        data: res.press,
    }, {
        id: 'chart-hum', 	                                        // Графік вологості
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

function fetchResult(result) {                                      // Допоміжна функція для відокремлення окремих 
    var temp = [];                                                  // показників від загальних даних, отриманих в БД
    var press = [];
    var hum = [];
    var date = [];

    result.forEach(element => {
        temp.push(element.temp_bme280);
        press.push(element.press_bme280);
        hum.push(element.hum_bme280);
        date.push(element.date_bme280);
    });

    return { temp, press, hum, date };
}

function createConfig(labels, data, colorName) {                    // допоміжна ф-ція для налаштування виводу графіків
    var pointRadius;
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

function toggleChart(id) {                                          // Функція збільшення графіка при натисканні
    var element = document.getElementById(id.id);
    element.classList.toggle("large");
}

/* --------------------------------- Helpers ---------------------------------*/

function printError(jqXHR, exception, dest) {                       // Функція виводу помилки запиту
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
    localStorage.setItem("ServerError", msg);
    $(dest).text("" + msg);
}

function getItems() {                                               // Функція зчитування параметрів з локального сховища браузера
    var items = {                                                   // та встановлення стандартних значень
        page: getLocalStorageItem("page", 1),
        count: getLocalStorageItem("count", COUNTER_LIST[0]),
        param: getLocalStorageItem("param", OPTIONS[0].value),
        order: getLocalStorageItem("order", "DESC"),
        city: getLocalStorageItem("city", "Київ"),
        ledSlider: getLocalStorageItem("ledSlider", 255),
    };

    return items;
}

function getLocalStorageItem(name, defaultValue) {                  // Функція отримання параметра з локального сховища
    var item = localStorage.getItem(name);                          // при його відсутності встановити стандартне значення
    if (item == null) {
        localStorage.setItem(name, defaultValue);
        return defaultValue;
    }
    return item;
}

// Get slider value from database and set it to the item
function getSlider() {
    sliderValue = getLedSliderValue();
    localStorage.setItem("ledSlider", sliderValue);
};

function updateSlider() {
    var sliderValue = document.getElementById("ledSlider").value;
    localStorage.setItem("ledSlider", sliderValue);
};