<?php
require 'db.php';

$cart        = $_SESSION['cart'] ?? [];
$total_items = 0;
$subtotal    = 0;

foreach ($cart as $entry) {
  $total_items += $entry['qty'];
  $line_total   = $entry['base_price'] * $entry['qty'];

  if (!empty($entry['addons'])) {
    $ids          = $entry['addons'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));
    $stmt         = $connection->prepare("SELECT SUM(price) as total FROM add_on_items WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $addon_total  = $stmt->get_result()->fetch_assoc()['total'] ?? 0;
    $line_total  += $addon_total * $entry['qty'];
  }

  foreach ($entry['sides'] ?? [] as $side_id) {
    $side_qty     = (int)($entry['side_qtys'][$side_id] ?? 1);
    $total_items += $side_qty;
    $stmt = $connection->prepare("SELECT `base-price` FROM appetizer_items WHERE id = ?");
    $stmt->bind_param("i", $side_id);
    $stmt->execute();
    $side_price  = $stmt->get_result()->fetch_assoc()['base-price'] ?? 0;
    $line_total += $side_price * $side_qty;
  }

  foreach ($entry['drinks'] ?? [] as $drink_id) {
    $drink_qty    = (int)($entry['drink_qtys'][$drink_id] ?? 1);
    $total_items += $drink_qty;
    $stmt = $connection->prepare("SELECT `base-price` FROM drink_items WHERE id = ?");
    $stmt->bind_param("i", $drink_id);
    $stmt->execute();
    $drink_price  = $stmt->get_result()->fetch_assoc()['base-price'] ?? 0;
    $line_total  += $drink_price * $drink_qty;
  }

  $subtotal += $line_total;
}

$credit_charge = round($subtotal * 0.0395, 2);
$base_total    = round($subtotal + $credit_charge, 2);
?>


<!DOCTYPE html>
<html lang="en">o
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Payment | Kami Food Truck</title>
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/_base.css">
  <link rel="stylesheet" href="css/_chrome.css">
  <link rel="stylesheet" href="css/_controls.css">
  <link rel="stylesheet" href="css/_buttons.css">
  <link rel="stylesheet" href="css/_page.css">
  <link rel="stylesheet" href="css/_responsive.css">
  <link rel="stylesheet" href="css/_additional.css">
</head>
<body>
  <main>
    <header></header>

    <div><img src="images/background.png" alt="red"></div>

    <div style="padding: 0.75rem var(--pad) 0;">
      <a onclick="history.back()" class="btn btn--back">&#10094;</a>
    </div>

    <div class="page">

      <div class="option">
        <span class="bold">Pick-Up at</span>
        <div class="time-wrap">
          <span class="badge clickable" id="time-toggle">11:20am <span id="time-arrow" style="display:inline-block; transition: transform 0.3s ease;">▼</span></span>
          <div class="time-picker" id="time-picker">
            <div class="time-option active" data-time="11:20am">11:20am</div>
            <div class="time-option" data-time="11:30am">11:30am</div>
            <div class="time-option" data-time="11:40am">11:40am</div>
            <div class="time-option" data-time="11:50am">11:50am</div>
            <div class="time-option" data-time="12:00pm">12:00pm</div>
            <div class="time-option" data-time="12:10pm">12:10pm</div>
          </div>
        </div>
      </div>

      <h3 class="bold">Payment Method</h3><br>
      <div class="col pay-methods" id="methods">

        <div class="pill pill-method clickable" id="pay-card">
          <div class="radio"></div>
          <span class="bold">Card</span>
        </div>

        <div class="pill pill-method" style="pointer-events: none;">
          <div class="radio"></div>
          <span class="bold">Apple Pay</span>
        </div>
        <div class="pill pill-method" style="pointer-events: none;">
          <div class="radio"></div>
          <span class="bold">Venmo</span>
        </div>
        <div class="pill pill-method" style="pointer-events: none;">
          <div class="radio"></div>
          <span class="bold">Zelle</span>
        </div>

      </div>

      <hr>

      <section>
        <h3>Add Utensils?</h3>
        <div class="option clickable" id="fork-option">
          <span class="check">✓</span>
          <span class="bold">Fork</span>
        </div>
        <div class="option clickable" id="chop-option">
          <span class="check">✓</span>
          <span class="bold">Chopsticks</span>
        </div>
      </section>

      <section>
        <h3>Tip?</h3>
        <div class="row">
          <div class="pill pill-tip clickable" data-tip="1">$1</div>
          <div class="pill pill-tip clickable" data-tip="2">$2</div>
          <div class="pill pill-tip clickable" data-tip="3">$3</div>
          <div class="pill pill-tip clickable" data-tip="4">$4</div>
        </div>
      </section>

      <section class="summary" id="totals">
        <div class="row">
          <span>Subtotal</span>
          <span>$<?php echo number_format($subtotal, 2); ?></span>
        </div>
        <div class="row">
          <span>3.95% Credit Charge</span>
          <span>$<?php echo number_format($credit_charge, 2); ?></span>
        </div>
        <div class="row" id="tip-row" style="display: none;">
          <span>Tip</span><span id="tip-amount">$0.00</span>
        </div>
        <div class="row total">
          <span>Total (<?php echo $total_items; ?> item<?php echo $total_items !== 1 ? 's' : ''; ?>)</span>
          <span id="total-amount">$<?php echo number_format($base_total, 2); ?></span>
        </div>
      </section>

      <hr><br>
      <form method="POST" action="checkout.php" id="pay-form">
        <input type="hidden" name="pickup_time" id="input-pickup-time" value="11:20am">
        <input type="hidden" name="tip"         id="input-tip"         value="0">
        <input type="hidden" name="base_total"  value="<?php echo $base_total; ?>">
        <button type="submit" class="btn disabled" id="pay-btn">Pay</button>
      </form>
    </div>
  </main>

  <script>
    const cardBtn = document.getElementById('pay-card');
    const payBtn  = document.getElementById('pay-btn');
    let cardSelected = false;
    let cardFields = null;

    /* -- Build + inject card fields -------------- */
    function buildCardFields() {
      const wrap = document.createElement('div');
      wrap.id = 'card-fields';
      wrap.className = 'card-fields';

      wrap.appendChild(makeInput('card-num', '0000 0000 0000 0000', 'numeric', 19));

      const rowA = makeRow([
        makeInput('card-exp',  'MM/YY',  'numeric', 5),
        makeInput('card-cvv',  'CVV',    'numeric', 3),
      ]);
      const rowB = makeRow([
        makeInput('card-name', 'Name on Card', 'text',    30),
        makeInput('card-zip',  'ZIP Code',     'numeric',  5),
      ]);

      wrap.appendChild(rowA);
      wrap.appendChild(rowB);
      return wrap;
    }

    function makeInput(id, placeholder, inputmode, maxlength) {
      const inp = document.createElement('input');
      inp.type = 'text';
      inp.className = 'field card-required';
      inp.id = id;
      inp.placeholder = placeholder;
      inp.setAttribute('inputmode', inputmode);
      inp.maxLength = maxlength;
      inp.addEventListener('input', validateCard);
      return inp;
    }

    function makeRow(inputs) {
      const row = document.createElement('div');
      row.className = 'row';
      inputs.forEach(inp => {
        const d = document.createElement('div');
        d.appendChild(inp);
        row.appendChild(d);
      });
      return row;
    }

    function attachFormatters() {
      cardFields.querySelector('#card-num').addEventListener('input', function () {
        const d = this.value.replace(/\D/g, '').substring(0, 16);
        this.value = d.replace(/(.{4})/g, '$1 ').trim();
      });
      cardFields.querySelector('#card-exp').addEventListener('input', function () {
        const d = this.value.replace(/\D/g, '').substring(0, 4);
        this.value = d.length >= 3 ? `${d.slice(0,2)}/${d.slice(2)}` : d;
      });
      cardFields.querySelector('#card-cvv').addEventListener('input',  function () { this.value = this.value.replace(/\D/g, ''); });
      cardFields.querySelector('#card-name').addEventListener('input', function () { this.value = this.value.replace(/[^A-Za-z\s\-]/g, ''); });
      cardFields.querySelector('#card-zip').addEventListener('input',  function () { this.value = this.value.replace(/\D/g, ''); });
    }

    function validateCard() {
  if (!cardFields) return;

      const cardNum  = cardFields.querySelector('#card-num').value.replace(/\s/g, '');
      const cardExp  = cardFields.querySelector('#card-exp').value.replace(/\D/g, '');
      const cardCvv  = cardFields.querySelector('#card-cvv').value;
      const cardName = cardFields.querySelector('#card-name').value.trim();
      const cardZip  = cardFields.querySelector('#card-zip').value;

      const reasons = [];
      if (cardNum.length  < 16) reasons.push('card number');
      if (cardExp.length  < 4)  reasons.push('expiry date');
      if (cardCvv.length  < 3)  reasons.push('CVV');
      if (cardName.length < 1)  reasons.push('name on card');
      if (cardZip.length  < 5)  reasons.push('ZIP code');

      const valid = reasons.length === 0;
      payBtn.className = (cardSelected && valid) ? 'btn clickable' : 'btn disabled';

      let hint = document.getElementById('pay-hint');
      if (!hint) {
        hint = document.createElement('p');
        hint.id = 'pay-hint';
        hint.style.cssText = 'font-size: 0.8rem; color: var(--dk); text-align: center; margin-top: 0.5rem; font-style: italic;';
        payBtn.insertAdjacentElement('afterend', hint);
      }
      hint.textContent = reasons.length > 0
        ? `Missing: ${reasons.join(', ')}`
        : '';
    }

    /* -- Card pill toggle ------------------------ */
    cardBtn.addEventListener('click', () => {
      cardSelected = !cardSelected;
      cardBtn.classList.toggle('active', cardSelected);
      cardBtn.querySelector('.radio').classList.toggle('active', cardSelected);

      if (cardSelected) {
        cardFields = buildCardFields();
        cardBtn.insertAdjacentElement('afterend', cardFields);
        // Trigger transition on next frame
        requestAnimationFrame(() => cardFields.classList.add('visible'));
        attachFormatters();
      } else {
        cardFields.classList.remove('visible');
        cardFields.addEventListener('transitionend', () => {
          cardFields.remove();
          cardFields = null;
        }, { once: true });
        payBtn.className = 'btn disabled';
      }
    });

    /* -- Time picker ----------------------------- */
    const timeToggle = document.getElementById('time-toggle');
    const timePicker = document.getElementById('time-picker');
    const timeArrow  = document.getElementById('time-arrow');

    timeToggle.addEventListener('click', () => {
      timePicker.classList.toggle('visible');
      timeToggle.classList.toggle('open');
      timeArrow.style.transform = timePicker.classList.contains('visible') ? 'rotate(180deg)' : 'rotate(0deg)';
    });
    document.querySelectorAll('.time-option').forEach(opt => {
      opt.addEventListener('click', () => {
        document.querySelectorAll('.time-option').forEach(o => o.classList.remove('active'));
        opt.classList.add('active');
        timeToggle.querySelector('span').textContent = '▼';
        timeToggle.firstChild.textContent = opt.dataset.time + ' ';
        timeArrow.style.transform = 'rotate(0deg)';
        timePicker.classList.remove('visible');
        timeToggle.classList.remove('open');
        document.getElementById('input-pickup-time').value = opt.dataset.time; // ← add this
      });
    });

    /* -- Utensil toggles ------------------------- */
    document.querySelectorAll('#fork-option, #chop-option').forEach(opt => {
      opt.addEventListener('click', () => opt.querySelector('.check').classList.toggle('active'));
    });

    /* -- Tip selection --------------------------- */
    const tipButtons  = document.querySelectorAll('.pill-tip');
    const tipRow      = document.getElementById('tip-row');
    const tipAmount   = document.getElementById('tip-amount');
    const totalAmount = document.getElementById('total-amount');
    const BASE_TOTAL  = <?php echo $base_total; ?>;
    let currentTip = 0;

    tipButtons.forEach(btn => {
      btn.addEventListener('click', () => {
        const tip = parseInt(btn.dataset.tip);
        if (currentTip === tip) {
          btn.classList.remove('active');
          currentTip = 0;
          tipRow.style.display = 'none';
        } else {
          tipButtons.forEach(b => b.classList.remove('active'));
          btn.classList.add('active');
          currentTip = tip;
          tipAmount.textContent = `$${tip.toFixed(2)}`;
          tipRow.style.display = 'flex';
        }
        totalAmount.textContent = `$${(BASE_TOTAL + currentTip).toFixed(2)}`;
        document.getElementById('input-tip').value = currentTip; // ← add this
      });
    });
  </script>
</body>
</html>