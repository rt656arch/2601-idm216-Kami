<?php
require 'db.php';

$cart = $_SESSION['cart'] ?? [];

// Build totals
$total_items = 0;
$total_price = 0;

foreach ($cart as $entry) {
  $total_items += $entry['qty'];
  $line_total   = $entry['base_price'] * $entry['qty'];

  // Add the prices of the add on into total
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
  $stmt = $connection->prepare("SELECT `base-price` FROM appetizer_items WHERE id = ?");
  $stmt->bind_param("i", $side_id);
  $stmt->execute();
  $side_price = $stmt->get_result()->fetch_assoc()['base-price'] ?? 0;
  $side_qty   = $entry['side_qtys'][$side_id] ?? 1;  // use independent qty
  $total_items += $side_qty;
  $line_total += $side_price * $side_qty;             // not $entry['qty']
}

foreach ($entry['drinks'] ?? [] as $drink_id) {
  $stmt = $connection->prepare("SELECT `base-price` FROM drink_items WHERE id = ?");
  $stmt->bind_param("i", $drink_id);
  $stmt->execute();
  $drink_price = $stmt->get_result()->fetch_assoc()['base-price'] ?? 0;
  $drink_qty   = $entry['drink_qtys'][$drink_id] ?? 1;  // use independent qty
  $total_items += $drink_qty;  
  $line_total += $drink_price * $drink_qty;              // not $entry['qty']
}

  $total_price += $line_total;
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Bag | Kami Food Truck</title>
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="css/_base.css">
  <link rel="stylesheet" href="css/_chrome.css">
  <link rel="stylesheet" href="css/_controls.css">
  <link rel="stylesheet" href="css/_buttons.css">
  <link rel="stylesheet" href="css/_bag.css">
  <link rel="stylesheet" href="css/_responsive.css">
</head>

<body>
<main>
  <header></header>
  <div><img src="images/background.png" alt="red"></div>

  <?php if (empty($cart)): ?>

    <h2 class="bag-title">BAG</h2>
    <div class="bag-empty">
      <img src="images/bag_duck.png" alt="Kami mascot" style="margin-top: 2rem;">
      <a href="menu.php" class="btn">Order Now</a>
    </div>

  <?php else: ?>

  <div class="bag-header">
    <a onclick="history.back()" class="btn btn--back">&#10094;</a>
    <h2 class="bag-title">BAG</h2>
    <div class="bag-header-spacer"></div>
  </div>
  <hr class="bag-divider">

  <div class="bag-items">

      <?php foreach ($cart as $index => $entry):
          $image_name = str_replace(' ', '-', $entry['item_name']);
          $image_path = "./media/menu-images/" . $image_name . ".jpg";
        $line_total = $entry['base_price'] * $entry['qty'];
        $addon_names = [];

        if (!empty($entry['addons'])) {
          $ids          = $entry['addons'];
          $placeholders = implode(',', array_fill(0, count($ids), '?'));
          $types        = str_repeat('i', count($ids));
          $stmt         = $connection->prepare("SELECT name, price FROM add_on_items WHERE id IN ($placeholders)");
          $stmt->bind_param($types, ...$ids);
          $stmt->execute();
          $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

          foreach ($rows as $row) {
            $addon_names[] = $row['name'] . ' $' . number_format($row['price'], 2);
            $line_total   += $row['price'] * $entry['qty'];
          }
        }
      ?>

  <!-- Main item card -->
  <div class="bag-card" 
     data-index="<?php echo $index; ?>"
     data-price="<?php echo $entry['base_price']; ?>">
    <img src="<?php echo htmlspecialchars($image_path); ?>"
         alt="<?php echo htmlspecialchars($entry['item_name']); ?>">
    <div class="bag-card-body">
      <p class="bold"><?php echo htmlspecialchars($entry['item_name']); ?></p>

      <?php foreach ($addon_names as $addon_label): ?>
        <p class="muted italic">+ <?php echo htmlspecialchars($addon_label); ?></p>
      <?php endforeach; ?>

      <div class="bag-card-footer">
        <div class="qty small">
          <span class="qty-dec">−</span>
          <span class="qty-count"><?php echo $entry['qty']; ?></span>
          <span class="qty-inc">+</span>
        </div>
        <span class="bold">$<?php echo number_format($line_total, 2); ?></span>
      </div>
    </div>
    <a href="#" class="bag-remove" onclick="confirmRemove(event, <?php echo $index; ?>, 'item')">Remove</a>
  </div>

  <!-- Sides -->
  <?php foreach ($entry['sides'] ?? [] as $side_id):
    $stmt = $connection->prepare("SELECT name, `base-price` FROM appetizer_items WHERE id = ?");
    $stmt->bind_param("i", $side_id);
    $stmt->execute();
    $side = $stmt->get_result()->fetch_assoc();
    $side_name  = $side['name'] ?? '';
    $side_price = $side['base-price'] ?? 0;
    $side_image = "./media/menu-images/" . str_replace(' ', '-', $side_name) . ".jpg";
    $side_qty   = $entry['side_qtys'][$side_id] ?? 1;  // independent qty, defaults to 1
  ?>
  <div class="bag-card"
      data-index="<?php echo $index; ?>"
      data-side-id="<?php echo $side_id; ?>"
      data-price="<?php echo $side_price; ?>">
    <img src="<?php echo htmlspecialchars($side_image); ?>"
        alt="<?php echo htmlspecialchars($side_name); ?>">
    <div class="bag-card-body">
      <p class="bold"><?php echo htmlspecialchars($side_name); ?></p>
      <div class="bag-card-footer">
        <div class="qty small">
          <span class="qty-dec">−</span>
          <span class="qty-count"><?php echo $side_qty; ?></span>
          <span class="qty-inc">+</span>
        </div>
        <span class="bold">$<?php echo number_format($side_price * $side_qty, 2); ?></span>
      </div>
    </div>
    <a href="#"
      class="bag-remove"
      onclick="confirmRemove(event, <?php echo $index; ?>, 'side', <?php echo $side_id; ?>)">Remove</a>
  </div>
  <?php endforeach; ?>

  <!-- Drinks -->
  <?php foreach ($entry['drinks'] ?? [] as $drink_id):
    $stmt = $connection->prepare("SELECT name, `base-price` FROM drink_items WHERE id = ?");
    $stmt->bind_param("i", $drink_id);
    $stmt->execute();
    $drink = $stmt->get_result()->fetch_assoc();
    $drink_name  = $drink['name'] ?? '';
    $drink_price = $drink['base-price'] ?? 0;
    $drink_image = "./media/menu-images/" . str_replace(' ', '-', $drink_name) . ".jpg";
    $drink_qty   = $entry['drink_qtys'][$drink_id] ?? 1;  // independent qty, defaults to 1
  ?>
  <div class="bag-card"
      data-index="<?php echo $index; ?>"
      data-drink-id="<?php echo $drink_id; ?>"
      data-price="<?php echo $drink_price; ?>">
    <img src="<?php echo htmlspecialchars($drink_image); ?>"
        alt="<?php echo htmlspecialchars($drink_name); ?>">
    <div class="bag-card-body">
      <p class="bold"><?php echo htmlspecialchars($drink_name); ?></p>
      <div class="bag-card-footer">
        <div class="qty small">
          <span class="qty-dec">−</span>
          <span class="qty-count"><?php echo $drink_qty; ?></span>
          <span class="qty-inc">+</span>
        </div>
        <span class="bold">$<?php echo number_format($drink_price * $drink_qty, 2); ?></span>
      </div>
    </div>
    <a href="#"
      class="bag-remove"
      onclick="confirmRemove(event, <?php echo $index; ?>, 'drink', <?php echo $drink_id; ?>)">Remove</a>
  </div>
  <?php endforeach; ?>

<?php endforeach; ?>
<?php endif; ?>
</div>

  <div class="row total bag-total">
    <span>Total (<?php echo $total_items; ?> item<?php echo $total_items !== 1 ? 's' : ''; ?>)</span>
    <span>$<?php echo number_format($total_price, 2); ?></span>
  </div>

  <a href="payment.php" class="btn bag-continue">Continue</a>
  <br><br>

  <nav>
    <a href="menu.php">
      <img src="images/navigation-bar-menu.png" alt="Menu">
      <span>Menu</span>
    </a>
    <a href="home.php">
      <img src="images/navigation-bar-home.png" alt="Home">
      <span>Home</span>
    </a>
    <a href="bag.php" class="active">
      <img src="images/navigation-bar-bag.png" alt="Bag">
      <span>Bag</span>
    </a>
  </nav>
</main>

<script>
  // Qty controls
  document.querySelectorAll('.bag-card').forEach(card => {
    const decBtn  = card.querySelector('.qty-dec');
    const incBtn  = card.querySelector('.qty-inc');
    const qtyEl   = card.querySelector('.qty-count');
    const priceEl = card.querySelector('.bag-card-footer .bold');

    if (!decBtn || !incBtn) return;

    const index    = card.dataset.index;
    const sideId   = card.dataset.sideId   ?? null;
    const drinkId  = card.dataset.drinkId  ?? null;
    const price    = parseFloat(card.dataset.price);

    decBtn.addEventListener('click', () => {
      let qty = parseInt(qtyEl.textContent);
      if (qty <= 1) return;
      updateQty(index, qty - 1, qtyEl, priceEl, price, sideId, drinkId);
    });

    incBtn.addEventListener('click', () => {
      let qty = parseInt(qtyEl.textContent);
      updateQty(index, qty + 1, qtyEl, priceEl, price, sideId, drinkId);
    });
  });

  function updateQty(index, newQty, qtyEl, priceEl, price, sideId, drinkId) {
    qtyEl.textContent = newQty;
    if (priceEl && price) {
      priceEl.textContent = '$' + (price * newQty).toFixed(2);
    }

    let url = `cart.php?update_qty=${index}&qty=${newQty}`;
    if (sideId)  url += `&side_id=${sideId}`;
    if (drinkId) url += `&drink_id=${drinkId}`;

    fetch(url).then(() => location.reload());
  }

  function confirmRemove(e, index, type, id = null) {
    e.preventDefault();

    if (type === 'item') {
      const card      = document.querySelector(`.bag-card[data-index="${index}"]`);
      const itemName  = card.querySelector('.bold').textContent.trim();
      const hasSides  = document.querySelectorAll(`.bag-card[data-side-id][data-index="${index}"]`).length > 0;
      const hasDrinks = document.querySelectorAll(`.bag-card[data-drink-id][data-index="${index}"]`).length > 0;

      let message = `Remove ${itemName} from your bag?`;

      if (hasSides && hasDrinks) {
        message = `Remove ${itemName} and its associated sides and drinks from your bag?`;
      } else if (hasSides) {
        message = `Remove ${itemName} and its associated sides from your bag?`;
      } else if (hasDrinks) {
        message = `Remove ${itemName} and its associated drinks from your bag?`;
      }

      if (!confirm(message)) return;
      window.location.href = `cart.php?remove=${index}&confirm_all=1`;
      return;
    }

    if (type === 'side' || type === 'drink') {
      const card = document.querySelector(
        `.bag-card[data-${type}-id="${id}"][data-index="${index}"]`
      );
      const itemName = card ? card.querySelector('.bold').textContent.trim() : type;
      if (!confirm(`Remove ${itemName} from your bag?`)) return;
    }

    let url = '';
    if (type === 'side')  url = `cart.php?remove_side=${index}&id=${id}`;
    if (type === 'drink') url = `cart.php?remove_drink=${index}&id=${id}`;
    window.location.href = url;
  }
</script>

</body>
</html>