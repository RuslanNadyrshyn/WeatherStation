const sampleData = [];
for (let i = 0; i < 235; i++) {
  sampleData.push({
    time: `28.04.2025 ${(10 + (i % 14)).toString().padStart(2, '0')}:00`,
    temp: (20 + Math.random() * 5).toFixed(1),
    humidity: (40 + Math.random() * 20).toFixed(0),
    pressure: (1005 + Math.random() * 10).toFixed(0)
  });
}

const rowsPerPageSelect = document.getElementById('rowsPerPage');
const tbody = document.getElementById('data-table-body');
const pagination = document.getElementById('pagination');
const recordCount = document.getElementById('recordCount');
const toggleButton = document.getElementById('toggle-history'); //
const historySection = document.getElementById('history-section');
const tableWrapper = document.querySelector('.table-wrapper');
const headers = document.querySelectorAll('thead th');

let currentPage = 1;
let rowsPerPage = parseInt(rowsPerPageSelect.value);
let currentSort = { column: null, order: 'asc' };
let isExpanded = false;

rowsPerPageSelect.addEventListener('change', () => {
  rowsPerPage = rowsPerPageSelect.value === "all" ? sampleData.length : parseInt(rowsPerPageSelect.value);
  currentPage = 1;
  renderTable();
});

toggleButton.addEventListener('click', () => { //
  isExpanded = !isExpanded;
  if (isExpanded) {
    historySection.classList.add('expanded');
    toggleButton.textContent = "Сховати історію 🔼";
    renderTable();
    createChart();
  } else {
    historySection.classList.remove('expanded');
    toggleButton.textContent = "Показати історію 🔽";
  }
}); //

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
  tbody.innerHTML = '';

  headers.forEach(h => h.classList.remove('sorted'));

  let sortedData = [...sampleData];
  if (currentSort.column) {
    sortedData.sort((a, b) => {
      if (a[currentSort.column] < b[currentSort.column]) return currentSort.order === 'asc' ? -1 : 1;
      if (a[currentSort.column] > b[currentSort.column]) return currentSort.order === 'asc' ? 1 : -1;
      return 0;
    });

    const sortedHeader = Array.from(headers).find(h => h.dataset.column === currentSort.column);
    if (sortedHeader) {
      sortedHeader.classList.add('sorted');
    }
  }

  const start = (currentPage - 1) * rowsPerPage;
  const end = start + rowsPerPage;
  const pageData = sortedData.slice(start, end);

  pageData.forEach(entry => {
    const row = `<tr>
      <td>${entry.time}</td>
      <td>${entry.temp}</td>
      <td>${entry.humidity}</td>
      <td>${entry.pressure}</td>
    </tr>`;
    tbody.insertAdjacentHTML('beforeend', row);
  });

  renderPagination(sortedData.length);
  recordCount.innerText = `Всього записів: ${sampleData.length}`;

  tableWrapper.scrollTo({
    top: 0,
    behavior: 'smooth'
  });
}

function renderPagination(totalItems) {
  pagination.innerHTML = '';

  const pageCount = Math.ceil(totalItems / rowsPerPage);

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

  if (currentPage > 4) {
    addPageButton(1);
    addEllipsis();
  }

  for (let i = Math.max(1, currentPage - 3); i <= Math.min(pageCount, currentPage + 3); i++) {
    addPageButton(i);
  }

  if (currentPage + 3 < pageCount) {
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

function createChart() {
  // Тут можна підключити Chart.js або інший графік
}
