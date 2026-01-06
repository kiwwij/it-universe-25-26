let residents = [];
let messages = [];
let sortDirections = {};

// ---------------------------
// ЗАВАНТАЖЕННЯ ДАНИХ З JSON
// fetch("data/data.json")
//   .then(res => res.json())
//   .then(data => {
//     residents = data;
//     renderTable();
//     updateFreeApartments();
//   });

// ЗАВАНТАЖЕННЯ ДАНИХ З PHP
fetch("php/getResidents.php")
  .then(res => res.json())
  .then(data => {
    residents = data;
    renderTable();
    updateFreeApartments();
  })
  .catch(err => console.error("Помилка завантаження даних:", err));

// Розрахунок видимого балансу
function getVisibleBalance(r) {
  return r.currentBalance + r.paymentDue;
}

// Прихований борг за замовчуванням
let showDebt = false;

// Формула "До сплати"
function calculatePayment(r) {
  if (r.currentBalance >= 0) {
    return Math.max(5000 - r.currentBalance, 0);
  }
  return 5000;
}

// Борг
function calculateDebt(r) {
  return r.currentBalance < 0 ? Math.abs(r.currentBalance) : 0;
}

// Відобразити таблицю
function renderTable() {
  let tbody = document.querySelector("#residentsTable tbody");
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

// Кнопка показу/приховування боргів
document.getElementById("toggleDebt").addEventListener("click", () => {
  showDebt = !showDebt;
  document.querySelectorAll(".debt-col").forEach(col => {
    col.style.display = showDebt ? "table-cell" : "none";
  });
  document.getElementById("toggleDebt").textContent = showDebt ? "❌ Сховати борги" : "✅ Показати борги";
  renderTable();
});

// Додати мешканця
document.getElementById("addResidentForm").addEventListener("submit", function(e) {
  e.preventDefault();

  // Отримуємо список вільних квартир
  let freeApts = residents.filter(r => r.name === null || r.name === '').map(r => parseInt(r.apartment));
  if (freeApts.length === 0) return alert("Вільних квартир більше немає!");

  // Квартира, яку вибрав користувач
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

  // Відправка на сервер
  fetch('php/addResident.php', {
    method: 'POST',
    headers: { 
        'Content-Type': 'application/json; charset=UTF-8' // Додайте charset=UTF-8
    },
    body: JSON.stringify(newResident)
})
  .then(res => res.json())
  .then(data => {
    if (data.success) {
      // Оновлюємо масив локально
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

// Вільні квартири
function updateFreeApartments() {
  let select = document.getElementById("apartment");
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

// Пошук мешканця по номеру квартири
function searchResident() {
  let apt = parseInt(document.getElementById("searchId").value);
  let resBox = document.getElementById("searchResult");

  if (apt < 1 || apt > 54) {
    resBox.innerHTML = "<strong>У будинку тільки 54 квартири. Введіть коректний номер квартири.</strong>";
    return;
  }

  // Приводимо apartment до числа
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

// Пошук при натисканні Enter
document.getElementById("searchId").addEventListener("keydown", function(e) {
  if (e.key === "Enter") {
    e.preventDefault();
    searchResident();
  }
});

// Сортування таблиці з перемиканням напрямку
function sortTable(colIndex) {
  sortDirections[colIndex] = !sortDirections[colIndex]; 
  let direction = sortDirections[colIndex] ? 1 : -1;

  residents.sort((a, b) => {
    let valA, valB;

    switch (colIndex) {
      case 0: // ID
        valA = a.id; valB = b.id; break;
      case 1: // ПІБ
        valA = a.name; valB = b.name; break;
      case 2: // Квартира
        valA = a.apartment; valB = b.apartment; break;
      case 3: // Під’їзд
        valA = a.entrance; valB = b.entrance; break;
      case 4: // Площа
        valA = a.area; valB = b.area; break;
      case 5: // До сплати
        valA = calculatePayment(a); valB = calculatePayment(b); break;
      case 6: // Заборгованість
        valA = calculateDebt(a); valB = calculateDebt(b); break;
      default:
        valA = 0; valB = 0;
    }

    if (typeof valA === "number") return (valA - valB) * direction;
    return valA.toString().localeCompare(valB.toString(), "uk") * direction;
  });

  renderTable();
}

// Повідомлення
function sendMessage() {
  let text = document.getElementById("adminMessage").value.trim();
  if (text !== "") {
    messages.unshift(text);
    let msgList = document.getElementById("messages");
    msgList.innerHTML = messages.map(m => `<li>${m}</li>`).join("");
    document.getElementById("adminMessage").value = "";
  }
}

document.addEventListener('DOMContentLoaded', () => {
  const toggle = document.getElementById('themeToggle');
  const checkbox = toggle.querySelector('input');

  // Встановлюємо тему з localStorage або за замовчуванням світлу
  const savedTheme = localStorage.getItem('theme') || 'light';
  if (savedTheme === 'dark') {
    document.body.classList.add('dark-theme');
    checkbox.checked = true;
  } else {
    document.body.classList.remove('dark-theme');
    checkbox.checked = false;
  }

  // Перемикач теми
  toggle.addEventListener('change', () => {
    if (checkbox.checked) {
      document.body.classList.add('dark-theme');
      localStorage.setItem('theme', 'dark');
    } else {
      document.body.classList.remove('dark-theme');
      localStorage.setItem('theme', 'light');
    }
  });
});

// Повідомлення - завантаження та відправка
const sendBtn = document.querySelector('#sendMessageBtn');
const adminMessage = document.getElementById('adminMessage');
const messagesList = document.getElementById('messages');

function sendMessage() {
    const msg = adminMessage.value.trim();
    if (!msg) return;

    fetch('php/send_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'message=' + encodeURIComponent(msg)
    }).then(() => {
        adminMessage.value = '';
        loadMessages();
    });
}

function loadMessages() {
    fetch('php/get_messages.php')
        .then(res => res.json())
        .then(data => {
            messagesList.innerHTML = '';
            data.forEach(msg => {
                const li = document.createElement('li');
                li.className = 'message-item';

                // Текст повідомлення
                const spanText = document.createElement('span');
                spanText.textContent = `[${msg.sender}] ${msg.message} (${msg.created_at})`;
                li.appendChild(spanText);

                // Кнопка видалення
                const delBtn = document.createElement('button');
                delBtn.textContent = "🗑️";
                delBtn.className = 'delete-btn';
                delBtn.onclick = () => deleteMessage(msg.id);
                li.appendChild(delBtn);

                messagesList.appendChild(li);
            });
        });
}

function deleteMessage(id) {
    if (!confirm("Видалити це повідомлення?")) return;

    fetch('php/delete_message.php', {
        method: 'POST',
        headers: {'Content-Type': 'application/x-www-form-urlencoded'},
        body: 'id=' + encodeURIComponent(id)
    }).then(() => loadMessages());
}

// Автооновлення кожні 5 секунд
setInterval(loadMessages, 5000);
loadMessages();

  // ======= Завантаження таблиці =======
  function loadTable() {
    const table = document.getElementById('tableSelect').value;
    const container = document.getElementById('tableContainer');
    if (!table) {
      container.innerHTML = "";
      return;
    }

    fetch(`db_operations.php?action=load&table=${table}&admin_id=${currentAdminId}`)
        .then(res => res.text())
        .then(html => {
            document.getElementById('tableContainer').innerHTML = html;
        });
  }

  // ======= Додавання / редагування / видалення =======
  function editRow(table, id) {
    const inputs = document.querySelectorAll(`#row_${id} input`);
    const data = {};
    inputs.forEach(inp => data[inp.name] = inp.value);

    fetch('db_operations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'update', table, id, ...data })
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      loadTable();
    });
  }

  function deleteRow(table, id) {
    if (!confirm('Видалити цей запис?')) return;
    fetch('db_operations.php?action=delete&table=' + table + '&id=' + id)
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

    fetch('db_operations.php', {
      method: 'POST',
      headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
      body: new URLSearchParams({ action: 'add', table, ...data })
    })
    .then(res => res.text())
    .then(msg => {
      alert(msg);
      loadTable();
    });
  }

document.querySelectorAll('table input').forEach(input => {
    input.addEventListener('focus', () => {
        input.scrollIntoView({ behavior: 'smooth', block: 'center' });
    });
});

function confirmPayment() {
  const email = document.querySelector('#paymentForm input[type="email"]').value;
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
  paymentSection.appendChild(messageDiv);

  document.getElementById('paymentForm').reset();
  document.getElementById('paymentForm').style.display = 'none';
}

document.addEventListener("DOMContentLoaded", function () {
    const now = new Date();
    const month = now.getMonth() + 1;
    const day = now.getDate();

    // Період з 14 грудня по 14 січня
    const isWinterTime = (month === 12 && day >= 14) || (month === 1 && day <= 14);

    if (isWinterTime) {
        // Обираємо і хедер, і футер
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

        // Запускаємо генерацію для кожного контейнера
        snowContainers.forEach(container => {
            setInterval(() => createSnowflake(container), 400);
        });
    }
});