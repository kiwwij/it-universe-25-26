let selectedPlanName = "";
let selectedPlanPrice = 0;

document.getElementById('themeToggle').addEventListener('change', (e) => {
  document.body.classList.toggle('dark-theme', e.target.checked);
});

function choosePlan(name, price) {
  selectedPlanName = name;
  selectedPlanPrice = price;
  document.getElementById('paymentSection').style.display = 'block';
  document.getElementById('planInfo').innerText = `План: ${name} (₴${price})`;
  window.scrollTo({ top: document.getElementById('paymentSection').offsetTop, behavior: 'smooth' });
}

async function confirmPayment() {
  const name = document.getElementById('cardName').value;
  const email = document.getElementById('email').value;
  const card = document.getElementById('cardNumber').value;

  if (!name || !email || card.length < 16) {
    alert('Заповніть форму коректно!');
    return;
  }

  const orderId = Math.floor(Math.random() * 900000) + 100000;

  document.getElementById('rNumber').innerText = orderId;
  document.getElementById('rDate').innerText = new Date().toLocaleString();
  document.getElementById('rPlan').innerText = selectedPlanName;
  document.getElementById('rAmount').innerText = `₴${selectedPlanPrice}`;
  document.getElementById('rName').innerText = name;
  document.getElementById('rEmail').innerText = email;
  document.getElementById('rCard').innerText = card.replace(/\d(?=\d{4})/g, "*");

  document.getElementById('receiptModal').style.display = 'block';

  fetch('php/send_receipt.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, plan: selectedPlanName, price: selectedPlanPrice, orderId })
    }).then(res => res.json()).then(data => console.log("Email status:", data));
}

function downloadPDF() {
    const orderId = document.getElementById('rNumber').innerText;
    const name = document.getElementById('rName').innerText;
    const email = document.getElementById('rEmail').innerText;
    const plan = document.getElementById('rPlan').innerText;
    const price = document.getElementById('rAmount').innerText.replace('₴', '');

    const url = `php/send_receipt.php?orderId=${orderId}&name=${encodeURIComponent(name)}&email=${encodeURIComponent(email)}&plan=${encodeURIComponent(plan)}&price=${price}`;
        
    window.location.href = url;
}

document.getElementById('cardNumber').addEventListener('input', function(e) {
  e.target.value = e.target.value.replace(/\D/g, '').replace(/(.{4})/g, '$1 ').trim();
});

function sendFeedback(event) {
    event.preventDefault();
    
    const name = document.getElementById('fbName').value;
    const email = document.getElementById('fbEmail').value;
    const message = document.getElementById('fbMessage').value;
    const btn = document.getElementById('fbSubmitBtn');
    const statusBox = document.getElementById('fbStatus');

    btn.disabled = true;
    btn.innerText = 'Відправка...⏳';

    fetch('php/send_feedback.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ name, email, message })
    })
    .then(res => res.json())
    .then(data => {
        statusBox.style.display = 'block';
        if (data.success) {
            statusBox.style.backgroundColor = '#dcfce7';
            statusBox.style.color = '#16a34a';
            statusBox.style.border = '1px solid #16a34a';
            statusBox.innerText = '✅ Ваше повідомлення успішно відправлено!';
            document.getElementById('feedbackForm').reset();
        } else {
            statusBox.style.backgroundColor = '#fee2e2';
            statusBox.style.color = '#dc2626';
            statusBox.style.border = '1px solid #dc2626';
            statusBox.innerText = '❌ Помилка: ' + (data.error || 'Не вдалося відправити.');
        }
    })
    .catch(err => {
        statusBox.style.display = 'block';
        statusBox.style.backgroundColor = '#fee2e2';
        statusBox.style.color = '#dc2626';
        statusBox.style.border = '1px solid #dc2626';
        statusBox.innerText = '❌ Помилка з\'єднання із сервером.';
        console.error(err);
    })
    .finally(() => {
        btn.disabled = false;
        btn.innerText = 'Відправити листа';
        setTimeout(() => { statusBox.style.display = 'none'; }, 5000);
    });
}