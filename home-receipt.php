<?php
  require_once './db.php';

  $order_id = (int)($_GET['order_id'] ?? 0);
  if ($order_id <= 0) { header('Location: menu.php'); exit; }

  // Fetch order
  $stmt = $connection->prepare("SELECT * FROM kami_orders WHERE id = ?");
  $stmt->bind_param("i", $order_id);
  $stmt->execute();
  $order = $stmt->get_result()->fetch_assoc();
  if (!$order) { header('Location: menu.php'); exit; }

  // Fetch order items
  $stmt = $connection->prepare("SELECT * FROM kami_order_items WHERE order_id = ?");
  $stmt->bind_param("i", $order_id);
  $stmt->execute();
  $line_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

  // Count total items and build grouped structure
  $total_items = 0;
  $grouped     = [];
  foreach ($line_items as $line) {
    if ($line['type'] === 'item') {
      $total_items += $line['quantity'] > 0 ? $line['quantity'] : 1;
      $grouped[] = ['main' => $line, 'addons' => [], 'sides' => [], 'drinks' => []];
    } else {
      $last = count($grouped) - 1;
      if ($last >= 0) {
        $key = match($line['type']) {
          'addon' => 'addons',
          'side'  => 'sides',
          'drink' => 'drinks',
          default => 'addons'
        };
        $grouped[$last][$key][] = $line;
        // count sides and drinks as their own items
        if ($line['type'] === 'side' || $line['type'] === 'drink') {
          $total_items += $line['quantity'] > 0 ? $line['quantity'] : 1;
        }
      }
    }
  }

  $credit_charge = round($order['subtotal'] * 0.0395, 2);
  $connection->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Home | Kami Food Truck</title>
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/_base.css">
  <link rel="stylesheet" href="css/_chrome.css">
  <link rel="stylesheet" href="css/_buttons.css">
  <link rel="stylesheet" href="css/_cards.css">
  <link rel="stylesheet" href="css/_home.css">
  <link rel="stylesheet" href="css/_page.css">
  <link rel="stylesheet" href="css/_responsive.css">
</head>
<body>
  <main>
    <header></header>
    <div><img src="images/background.png" alt="red"></div>

    <div class="greeting">
      <h1 style="font-size: 2rem;"><span class="korean">안녕,</span> We got your order!</h1>
    </div>

    <!-- Confirmation GIF (hidden after 5s, order revealed) -->
    <div class="gif" id="gif-area">
      <img src="images/no-loop-chopsticks.gif" alt="Order confirmed animation">
    </div>

    <!-- PHP: $order = current order from DB -->
    <div class="order" id="order-area">

      <div class="progress clickable" id="progress-toggle">
        <div class="progress-header">
          <h3>Order #<?php echo $order_id; ?> <span style="font-size: 0.65em;">Progress</span></h3>
          <span class="progress-arrow" id="arrow" style="font-size: 1.4rem; line-height: 1;">▽</span>
        </div>
        <div class="bar"><div id="progress-bar"></div></div>
        <div class="status">
          <p id="status-text" class="visible">6 minutes until your order is ready!</p>
        </div>
      </div>

      <div class="receipt" id="receipt-area">

        <a href="https://www.google.com/maps/dir/?api=1&destination=33rd+and+Market+Philadelphia+PA+19148" target="_blank" class="receipt-link" style="display: flex; flex-direction: column; align-items: center; text-align: center; gap: 0.15rem;">
          📍<br>TAP TO VIEW LOCATION
        </a>

        <div class="thanks">
          <h2>Thank You For Ordering!</h2>
          <h4>Pickup Time: <?php echo htmlspecialchars($order['pickup_time'])?></h4>
          <p>We are glad you decided to choose the Kami Food Truck! We put genuine care into every part of our meals.</p>
        </div>

    <table>
      <thead>
        <tr><th>QTY</th><th>ITEM</th><th>PRICE</th></tr>
      </thead>
        <!-- PHP: foreach ($order->items as $item) { ... } -->
        <tbody>
          <?php foreach ($grouped as $group):
            $addon_total = 0;
            foreach ($group['addons'] as $addon) {
              $addon_total += $addon['price'];
            }
            $line_total = ($group['main']['price'] + $addon_total) * $group['main']['quantity'];
          ?>
          <tr>
            <td><?php echo $group['main']['quantity']; ?></td>
            <td>
              <span class="bold"><?php echo htmlspecialchars($group['main']['item_name']); ?></span>
              <?php foreach ($group['addons'] as $addon): ?>
                <br><span class="muted">+ <?php echo htmlspecialchars($addon['item_name']); ?></span>
              <?php endforeach; ?>
            </td>
            <td>$<?php echo number_format($line_total, 2); ?></td>
          </tr>

          <?php foreach ($group['sides'] as $side): ?>
          <tr>
            <td><?php echo $side['quantity']; ?></td>
            <td><span class="bold"><?php echo htmlspecialchars($side['item_name']); ?></span></td>
            <td>$<?php echo number_format($side['price'] * $side['quantity'], 2); ?></td>
          </tr>
          <?php endforeach; ?>

          <?php foreach ($group['drinks'] as $drink): ?>
          <tr>
            <td><?php echo $drink['quantity']; ?></td>
            <td><span class="bold"><?php echo htmlspecialchars($drink['item_name']); ?></span></td>
            <td>$<?php echo number_format($drink['price'] * $drink['quantity'], 2); ?></td>
          </tr>
          <?php endforeach; ?>

          <?php endforeach; ?>
        </tbody>
      </table>

        <div class="row">
          <span class="bold">ORDER #<?php echo $order_id; ?></span>
          <span class="muted">Order Placed: <?php echo date('m/d/Y g:iA', strtotime($order['created_at'])); ?></span>
        </div>

        <div class="summary">
          <div class="row"><span>Subtotal</span><span>$<?php echo number_format($order['subtotal'], 2); ?></span></div>
          <div class="row"><span>3.95% Credit Charge</span><span>$<?php echo number_format($credit_charge, 2); ?></span></div>
          <?php if ($order['tip'] > 0): ?>
          <div class="row"><span>Tip</span><span>$<?php echo number_format($order['tip'], 2); ?></span></div>
          <?php endif; ?>
          <div class="row total">
            <span>Total (<?php echo $total_items; ?> item<?php echo $total_items !== 1 ? 's' : ''; ?>)</span>
            <span>$<?php echo number_format($order['total'], 2); ?></span>
          </div>
        </div>
        <hr><br>
      </div>
    </div>

    <br>


    <!-- Popular carousel -->
    <!-- PHP: foreach ($popular as $item) { ... } -->
    <h2 class="label">Popular:</h2>
    <div class="carousel">

      <a href="customize.html" class="slide active">
        <img src="images/tteokbokki-ellipse.png" alt="Tteokbokki">
        <div class="slide-info">
          <div>
            <p class="korean-label">떡볶이</p>
            <p class="bold">Tteokbokki</p>
          </div>
          <span class="slide-price">$11</span>
        </div>
      </a>

      <a href="customize.html" class="slide">
        <img src="images/bibimbap-ellipse.png" alt="Bibimbap">
        <div class="slide-info">
          <div>
            <p class="korean-label">비빔밥</p>
            <p class="bold">Bibimbap</p>
          </div>
          <span class="slide-price">$11</span>
        </div>
      </a>

      <a href="customize.html" class="slide">
        <img src="images/bulgogi-ellipse.png" alt="Bulgogi">
        <div class="slide-info">
          <div>
            <p class="korean-label">불고기</p>
            <p class="bold">Bulgogi</p>
          </div>
          <span class="slide-price">$12</span>
        </div>
      </a>

      <a href="customize.html" class="slide">
        <img src="images/kimchi-fried-rice-ellipse.png" alt="Kimchi Fried Rice">
        <div class="slide-info">
          <div>
            <p class="korean-label">김치볶음밥</p>
            <p class="bold">Kimchi Fried Rice</p>
          </div>
          <span class="grid-price">$11</span>
        </div>
      </a>

      <a href="customize.html" class="slide">
        <img src="images/mandu-ellipse.png" alt="Mandu">
        <div class="slide-info">
          <div>
            <p class="korean-label">만두</p>
            <p class="bold">Mandu</p>
          </div>
          <span class="slide-price">$5</span>
        </div>
      </a>

    </div>

    <br>

    <!-- Worth Trying grid -->
    <!-- PHP: foreach ($worth_trying as $item) { ... } -->
    <div class="grid">
      <h2 class="label" style="grid-column: 1 / -1; margin-left: 0; justify-self: start;">Worth Trying:</h2>

      <a href="customize.html" class="grid-item">
        <img src="images/kimchi-fried-rice-ellipse.png" alt="Kimchi Fried Rice">
        <div>
          <p class="muted">김치볶음밥</p>
          <p class="bold">Kimchi Fried Rice</p>
          <p class="slide-price" style="text-align: right;">$11</p>
        </div>
      </a>

      <a href="customize.html" class="grid-item">
        <img src="images/mandu-ellipse.png" alt="Ramen">
        <div>
          <p class="muted">라면</p>
          <p class="bold">Ramen</p>
          <p class="slide-price" style="text-align: right;">$9</p>
        </div>
      </a>

      <a href="customize.html" class="grid-item">
        <img src="images/bibimbap-ellipse.png" alt="Bibimbap">
        <div>
          <p class="muted">비빔밥</p>
          <p class="bold">Bibimbap</p>
          <p class="slide-price" style="text-align: right;">$11</p>
        </div>
      </a>

      <a href="customize.html" class="grid-item">
        <img src="images/bulgogi-ellipse.png" alt="Bulgogi">
        <div>
          <p class="muted">불고기</p>
          <p class="bold">Bulgogi</p>
          <p class="slide-price" style="text-align: right;">$12</p>
          
        </div>
        
      </a>
      
    </div>
    
    <nav>
      <a href="menu.html">
        <img src="images/navigation-bar-menu.png" alt="Menu">
        <span>Menu</span>
      </a>
      <a href="home.html" class="active">
        <img src="images/navigation-bar-home.png" alt="Home">
        <span>Home</span>
      </a>
      <a href="bag-empty.html">
        <img src="images/navigation-bar-bag.png" alt="Bag">
        <span>Bag</span>
      </a>
    </nav>

  </main>

  <script>
    /* -- GIF to order reveal --------------------- */
    const gifArea     = document.getElementById('gif-area');
    const orderArea   = document.getElementById('order-area');
    const progressBar = document.getElementById('progress-bar');
    const statusText  = document.getElementById('status-text');

    const messages = [
      '6 minutes until your order is ready!',
      '5 minutes until your order is ready!',
      '4 minutes until your order is ready!',
      '3 minutes until your order is ready!',
      '1 minute until your order is ready!',
      'Your order is ready!'
    ];
    const widths = ['10%', '30%', '50%', '70%', '85%', '100%'];
    let step = 0;

    setTimeout(() => {
      gifArea.className = 'gif hidden';
      orderArea.className = 'order visible';
      setTimeout(() => {
        const timer = setInterval(() => {
          step++;
          if (step < messages.length) {
            progressBar.style.width = widths[step];
            statusText.textContent = messages[step];
          }
          if (step >= messages.length - 1) clearInterval(timer);
        }, 1500);
      }, 500);
    }, 3000);

    /* -- Receipt collapse toggle ----------------- */
    const toggle  = document.getElementById('progress-toggle');
    const receipt = document.getElementById('receipt-area');
    const arrow   = document.getElementById('arrow');

    toggle.addEventListener('click', () => {
    receipt.classList.toggle('collapsed');
    const isCollapsed = receipt.classList.contains('collapsed');
    arrow.style.transition = 'transform 0.30s ease';
    arrow.style.transform = isCollapsed ? 'rotate(-90deg)' : 'rotate(0deg)';
    arrow.textContent = isCollapsed ? '▼' : '▽';
  });
  </script>
</body>
</html>
