let residents = [];
let messages = [];
let sortDirections = {};

fetch("/php/getResidents.php")
  .then(res => res.json())
  .then(data => {
    residents = data;
    renderTable();
    updateFreeApartments();
  })
  .catch(err => console.error("Помилка завантаження даних:", err));

function getVisibleBalance(r) {
  return r.currentBalance + r.paymentDue;
}

let showDebt = false;

function calculatePayment(r) {
  if (r.currentBalance >= 0) {
    return Math.max(5000 - r.currentBalance, 0);
  }
  return 5000;
}

function calculateDebt(r) {
  return r.currentBalance < 0 ? Math.abs(r.currentBalance) : 0;
}

function renderTable() {
  let tbody = document.querySelector("#residentsTable tbody");
  if (!tbody) return;
  
  tbody.innerHTML = "";
  residents.forEach(r => {
    let payment = calculatePayment(r);
    let debt = calculateDebt(r);

    let balanceClass = "";
    if (r.currentBalance < 0) balanceClass = "negative";
    else if (r.currentBalance > 0) balanceClass = "positive";
    else balanceClass = "zero";

    tbody.innerHTML += `
      <tr>
        <td>${r.id}</td>
        <td>${r.name ?? ''}</td>
        <td>${r.apartment}</td>
        <td>${r.entrance}</td>
        <td>${r.area}</td>
        <td class="${balanceClass}">${payment} ₴</td>
        <td class="debt-col" style="display:${showDebt ? "table-cell" : "none"};">${debt} ₴</td>
      </tr>
    `;
  });
}

const toggleDebtBtn = document.getElementById("toggleDebt");
if (toggleDebtBtn) {
  toggleDebtBtn.addEventListener("click", () => {
    showDebt = !showDebt;
    document.querySelectorAll(".debt-col").forEach(col => {
      col.style.display = showDebt ? "table-cell" : "none";
    });
    toggleDebtBtn.textContent = showDebt ? "❌ Сховати борги" : "✅ Показати борги";
    renderTable();
  });
}

const addResidentForm = document.getElementById("addResidentForm");
if (addResidentForm) {
  addResidentForm.addEventListener("submit", function(e) {
    e.preventDefault();

    let freeApts = residents.filter(r => r.name === null || r.name === '').map(r => parseInt(r.apartment));
    if (freeApts.length === 0) return alert("Вільних квартир більше немає!");

    let apartment = parseInt(document.getElementById("apartment").value);
    if (!apartment || !freeApts.includes(apartment)) {
      return alert("Оберіть дійсно вільну квартиру!");
    }

    let entrance = apartment <= 27 ? 1 : 2;
    let newResident = {
      id: apartment,
      name: document.getElementById("name").value,
      apartment: apartment,
      entrance: entrance,
      area: residents.find(r => parseInt(r.apartment) === apartment)?.area ?? 0,
      currentBalance: parseFloat(document.getElementById("balance").value),
      paymentDue: 0,
      debt: 0
    };

    fetch('/php/addResident.php', {
      method: 'POST',
      headers: { 
          'Content-Type': 'application/json; charset=UTF-8'
      },
      body: JSON.stringify(newResident)
    })
    .then(res => res.json())
    .then(data => {
      if (data.success) {
        let index = residents.findIndex(r => parseInt(r.apartment) === apartment);
        residents[index] = newResident;

        renderTable();
        updateFreeApartments();
        this.reset();
        alert("Мешканця успішно додано!");
      } else {
        alert("Помилка додавання: " + data.error);
      }
    })
    .catch(err => alert("Помилка мережі: " + err));
  });
}

function updateFreeApartments() {
  let select = document.getElementById("apartment");
  if (!select) return;

  select.innerHTML = '<option value="">Оберіть вільну квартиру</option>';

  let freeApartments = residents
    .filter(r => r.name === null || r.name === '')
    .map(r => r.apartment);

  freeApartments.forEach(apt => {
    let option = document.createElement("option");
    option.value = apt;
    option.textContent = apt;
    select.appendChild(option);
  });
}

function searchResident() {
  let apt = parseInt(document.getElementById("searchId").value);
  let resBox = document.getElementById("searchResult");

  if (apt < 1 || apt > 54) {
    resBox.innerHTML = "<strong>У будинку тільки 54 квартири. Введіть коректний номер квартири.</strong>";
    return;
  }

  let r = residents.find(x => parseInt(x.apartment) === apt);
  
  if (r) {
    let balanceColor = r.currentBalance < 0 ? "red" : r.currentBalance > 0 ? "green" : "black";
    resBox.innerHTML = `
      <strong>Знайдено мешканця:</strong><br>
      ПІБ: ${r.name ?? ''}<br>
      Квартира: ${r.apartment}<br>
      Під’їзд: ${r.entrance}<br>
      Площа: ${r.area} м²<br>
      Поточний баланс: <span style="color:${balanceColor}; font-weight:bold;">${r.currentBalance} ₴</span>
    `;
  } else {
    resBox.innerHTML = "<strong>Мешканця з такою квартирою не знайдено.</strong>";
  }
}

const searchIdInput = document.getElementById("searchId");
if (searchIdInput) {
  searchIdInput.addEventListener("keydown", function(e) {
    if (e.key === "Enter") {
      e.preventDefault();
      searchResident();
    }
  });
}

function sortTable(colIndex) {
  sortDirections[colIndex] = !sortDirections[colIndex]; 
  let direction = sortDirections[colIndex] ? 1 : -1;

  residents.sort((a, b) => {
    let valA, valB;

    switch (colIndex) {
      case 0: valA = a.id; valB = b.id; break;
      case 1: valA = a.name; valB = b.name; break;
      case 2: valA = a.apartment; valB = b.apartment; break;
      case 3: valA = a.entrance; valB = b.entrance; break;
      case 4: valA = a.area; valB = b.area; break;
      case 5: valA = calculatePayment(a); valB = calculatePayment(b); break;
      case 6: valA = calculateDebt(a); valB = calculateDebt(b); break;
      default: valA = 0; valB = 0;
    }

    if (typeof valA === "number") return (valA - valB) * direction;
    return (valA || "").toString().localeCompare((valB || "").toString(), "uk") * direction;
  });

  renderTable();
}

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('themeToggle');
  if (toggle) {
    const checkbox = toggle.querySelector('input');
    const savedTheme = localStorage.getItem('theme') || 'light';
    
    if (savedTheme === 'dark') {
      document.body.classList.add('dark-theme');
      checkbox.checked = true;
    } else {
      document.body.classList.remove('dark-theme');
      checkbox.checked = false;
    }

    toggle.addEventListener('change', () => {
      if (checkbox.checked) {
        document.body.classList.add('dark-theme');
        localStorage.setItem('theme', 'dark');
      } else {
        document.body.classList.remove('dark-theme');
        localStorage.setItem('theme', 'light');
      }
    });
  }
});

const sendBtn = document.querySelector('#sendMessageBtn');
const adminMessage = document.getElementById('adminMessage');
const messagesList = document.getElementById('messages');

if (sendBtn && adminMessage && messagesList) {
    sendBtn.addEventListener('click', sendMessage);

    function sendMessage() {
        const msg = adminMessage.value.trim();
        if (!msg) return;

        fetch('/php/send_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'message=' + encodeURIComponent(msg)
        }).then(() => {
            adminMessage.value = '';
            loadMessages();
        });
    }

    function loadMessages() {
        fetch('/php/get_messages.php')
            .then(res => res.json())
            .then(data => {
                messagesList.innerHTML = '';
                data.forEach(msg => {
                    const li = document.createElement('li');
                    li.className = 'message-item';

                    const spanText = document.createElement('span');
                    spanText.textContent = `Admin: ${msg.message} (${msg.created_at})`;
                    li.appendChild(spanText);

                    const delBtn = document.createElement('button');
                    delBtn.textContent = "🗑️";
                    delBtn.className = 'delete-btn';
                    delBtn.onclick = () => deleteMessage(msg.id);
                    li.appendChild(delBtn);

                    messagesList.appendChild(li);
                });
            })
            .catch(err => console.error("Помилка завантаження повідомлень:", err));
    }

    function deleteMessage(id) {
        if (!confirm("Видалити це повідомлення?")) return;

        fetch('/php/delete_message.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'id=' + encodeURIComponent(id)
        }).then(() => loadMessages());
    }

    setInterval(loadMessages, 5000);
    loadMessages();
}

function loadTable() {
  const tableSelect = document.getElementById('tableSelect');
  const table = tableSelect ? tableSelect.value : 'residents';
  const container = document.getElementById('tableContainer');
  if (!container) return;

  if (!table) {
    container.innerHTML = "";
    return;
  }

  fetch(`/php/db_operations.php?action=load&table=${table}`)
      .then(res => res.text())
      .then(html => {
          container.innerHTML = html;
          makeTableResizable();
      });
}

function editRow(table, id) {
  const row = document.getElementById(`row_${id}`);
  const inputs = row.querySelectorAll(`input`);
  const data = {};
  inputs.forEach(inp => data[inp.name] = inp.value);

  const btn = row.querySelector("button[onclick^='editRow']");
  const originalText = btn ? btn.innerHTML : '💾';

  if (btn) btn.innerHTML = '⏳';

  fetch('/php/db_operations.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action: 'update', table, id, ...data })
  })
  .then(res => res.text())
  .then(msg => {
    if (msg.includes("✅")) {
        if (btn) {
            btn.innerHTML = '✅';
            btn.style.backgroundColor = '#059669';
            setTimeout(() => {
                btn.innerHTML = originalText;
                btn.style.backgroundColor = '';
            }, 2000);
        }
    } else {
        alert(msg);
        if (btn) btn.innerHTML = originalText;
    }
  })
  .catch(err => {
      alert("❌ Помилка з'єднання з сервером");
      if (btn) btn.innerHTML = originalText;
  });
}

function deleteRow(table, id) {
  if (!confirm('Видалити цей запис безповоротно?')) return;
  fetch(`/php/db_operations.php?action=delete&table=${table}&id=${id}`)
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      loadTable();
    });
}

function addRow(table) {
  const inputs = document.querySelectorAll('#addForm input');
  const data = {};
  inputs.forEach(inp => data[inp.name] = inp.value);

  fetch('/php/db_operations.php', {
    method: 'POST',
    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
    body: new URLSearchParams({ action: 'add', table, ...data })
  })
  .then(res => res.text())
  .then(msg => {
    alert(msg);
    if (msg.includes("✅")) {
        loadTable();
    }
  });
}

document.addEventListener('keydown', function(e) {
    if (e.key === 'Enter' && e.target.tagName === 'INPUT') {
        const row = e.target.closest('tr');
        if (row && row.id && row.id.startsWith('row_')) {
            const id = row.id.replace('row_', '');
            const tableSelect = document.getElementById('tableSelect');
            const table = tableSelect ? tableSelect.value : 'residents';
            
            editRow(table, id);
            e.target.blur();
        }
    }
});

function confirmPayment() {
  const emailInput = document.querySelector('#paymentForm input[type="email"]');
  if (!emailInput) return;
  
  const email = emailInput.value;
  const creds = generateCredentials();

  const messageDiv = document.createElement('div');
  messageDiv.style.marginTop = '15px';
  messageDiv.style.padding = '15px';
  messageDiv.style.backgroundColor = '#dcfce7';
  messageDiv.style.borderLeft = '4px solid #16a34a';
  messageDiv.style.borderRadius = '8px';
  messageDiv.innerHTML = `
    ✅ Оплату успішно проведено!<br>
    Ваш акаунт створено і сповіщення надіслано на <b>${email}</b>:<br>
    <b>Логін:</b> ${creds.login}<br>
    <b>Пароль:</b> ${creds.password}<br>
    Збережіть ці дані для входу.
  `;

  const paymentSection = document.getElementById('paymentSection');
  if (paymentSection) paymentSection.appendChild(messageDiv);

  const form = document.getElementById('paymentForm');
  if (form) {
    form.reset();
    form.style.display = 'none';
  }
}

document.addEventListener("DOMContentLoaded", function () {
    const now = new Date();
    const month = now.getMonth() + 1;
    const day = now.getDate();

    const isWinterTime = (month === 12 && day >= 14) || (month === 1 && day <= 14);

    if (isWinterTime) {
        const snowContainers = document.querySelectorAll(".header, .footer");
        const snowflakeSymbols = ['❄', '❅', '❆', '*'];

        function createSnowflake(container) {
            const snowflake = document.createElement("span");
            snowflake.classList.add("snowflake");
            
            snowflake.innerText = snowflakeSymbols[Math.floor(Math.random() * snowflakeSymbols.length)];
            snowflake.style.left = Math.random() * 100 + "%";
            
            const duration = Math.random() * 3 + 2 + "s";
            const opacity = Math.random();
            const size = Math.random() * 10 + 10 + "px";
            
            snowflake.style.animationDuration = duration;
            snowflake.style.opacity = opacity;
            snowflake.style.fontSize = size;

            container.appendChild(snowflake);

            setTimeout(() => {
                snowflake.remove();
            }, parseFloat(duration) * 1000);
        }

        snowContainers.forEach(container => {
            setInterval(() => createSnowflake(container), 400);
        });
    }
});

function makeTableResizable() {
    const table = document.querySelector('#tableContainer table');
    if (!table) return;

    const tableId = document.getElementById('tableSelect') ? document.getElementById('tableSelect').value : 'residents';
    const savedWidths = JSON.parse(localStorage.getItem(`col_widths_${tableId}`)) || {};

    const cols = table.querySelectorAll('th');
    cols.forEach((col, index) => {
        const colKey = `col_${index}`;
        
        if (savedWidths[colKey]) {
            col.style.width = savedWidths[colKey];
            col.style.minWidth = savedWidths[colKey];
        }

        const resizer = document.createElement('div');
        resizer.classList.add('resizer');
        col.appendChild(resizer);

        let x = 0;
        let w = 0;

        const mouseDownHandler = function(e) {
            x = e.clientX;
            const styles = window.getComputedStyle(col);
            w = parseInt(styles.width, 10);

            document.addEventListener('mousemove', mouseMoveHandler);
            document.addEventListener('mouseup', mouseUpHandler);
            
            resizer.classList.add('resizing');
        };

        const mouseMoveHandler = function(e) {
            const dx = e.clientX - x;
            const newWidth = `${w + dx}px`;
            col.style.width = newWidth;
            col.style.minWidth = newWidth;
        };

        const mouseUpHandler = function() {
            resizer.classList.remove('resizing');
            document.removeEventListener('mousemove', mouseMoveHandler);
            document.removeEventListener('mouseup', mouseUpHandler);

            savedWidths[colKey] = col.style.width;
            localStorage.setItem(`col_widths_${tableId}`, JSON.stringify(savedWidths));
        };

        resizer.addEventListener('mousedown', mouseDownHandler);
    });
}

document.getElementById('year').textContent = new Date().getFullYear();