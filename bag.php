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

  // Sides by ID
  foreach ($entry['sides'] ?? [] as $side_id) {
    $total_items++;
    $stmt = $connection->prepare("SELECT name, `base-price` FROM appetizer_items WHERE id = ?");
    $stmt->bind_param("i", $side_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $side_price  = $row['base-price'] ?? 0;
    $side_name   = $row['name'] ?? '';
    $side_image  = "images/" . str_replace(' ', '-', strtolower($side_name)) . "-ellipse.png";
    $line_total += $side_price * $entry['qty'];
  }

  // Drinks by ID
  foreach ($entry['drinks'] ?? [] as $drink_id) {
    $total_items++;
    $stmt = $connection->prepare("SELECT name, `base-price` FROM drink_items WHERE id = ?");
    $stmt->bind_param("i", $drink_id);
    $stmt->execute();
    $row = $stmt->get_result()->fetch_assoc();
    $drink_price  = $row['base-price'] ?? 0;
    $drink_name   = $row['name'] ?? '';
    $drink_image  = "images/" . str_replace(' ', '-', strtolower($drink_name)) . "-ellipse.png";
    $line_total  += $drink_price * $entry['qty'];
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

  <div class="bag-header">
    <a onclick="history.back()" class="btn btn--back">&#10094;</a>
    <h2 class="bag-title">BAG</h2>
    <div class="bag-header-spacer"></div>
  </div>
  <hr class="bag-divider">

  <div class="bag-items">

    <?php if (empty($cart)): ?>
      <p class="muted">Your bag is empty.</p>

    <?php else: ?>
      <?php foreach ($cart as $index => $entry):
        $image_name = str_replace(' ', '-', strtolower($entry['item_name']));
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
  <div class="bag-card" data-index="<?php echo $index; ?>">
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
    <a href="cart.php?remove=<?php echo $index; ?>" class="bag-remove">Remove</a>
  </div>

  <!-- Sides as their own cards -->
  <?php foreach ($entry['sides'] ?? [] as $side_id):
    $stmt = $connection->prepare("SELECT name, `base-price` FROM appetizer_items WHERE id = ?");
    $stmt->bind_param("i", $side_id);
    $stmt->execute();
    $side = $stmt->get_result()->fetch_assoc();
    $side_name  = $side['name'] ?? '';
    $side_price = $side['base-price'] ?? 0;
    $side_image = "./media/menu-images/" . str_replace(' ', '-', $side_name) . ".jpg";
    ?>
    <div class="bag-card">
      <img src="<?php echo htmlspecialchars($side_image); ?>"
          alt="<?php echo htmlspecialchars($side_name); ?>">
      <div class="bag-card-body">
        <p class="bold"><?php echo htmlspecialchars($side_name); ?></p>
        <div class="bag-card-footer">
          <div class="qty small">
            <span class="qty-dec">−</span>
            <span class="qty-count"><?php echo $entry['qty']; ?></span>
            <span class="qty-inc">+</span>
          </div>
          <span class="bold">$<?php echo number_format($side_price, 2); ?></span>
        </div>
      </div>
      <a href="cart.php?remove_side=<?php echo $index; ?>&id=<?php echo $side_id; ?>" class="bag-remove">Remove</a>
    </div>
  <?php endforeach; ?>

  <!-- Drinks as their own cards -->
  <?php foreach ($entry['drinks'] ?? [] as $drink_id):
    $stmt = $connection->prepare("SELECT name, `base-price` FROM drink_items WHERE id = ?");
    $stmt->bind_param("i", $drink_id);
    $stmt->execute();
    $drink = $stmt->get_result()->fetch_assoc();
    $drink_name  = $drink['name'] ?? '';
    $drink_price = $drink['base-price'] ?? 0;
    $drink_image = "./media/menu-images/" . str_replace(' ', '-', $drink_name) . ".jpg";
    ?>
    <div class="bag-card">
      <img src="<?php echo htmlspecialchars($drink_image); ?>"
          alt="<?php echo htmlspecialchars($drink_name); ?>">
      <div class="bag-card-body">
        <p class="bold"><?php echo htmlspecialchars($drink_name); ?></p>
        <div class="bag-card-footer">
          <div class="qty small">
            <span class="qty-dec">−</span>
            <span class="qty-count"><?php echo $entry['qty']; ?></span>
            <span class="qty-inc">+</span>
          </div>
          <span class="bold">$<?php echo number_format($drink_price, 2); ?></span>
        </div>
      </div>
      <a href="cart.php?remove_drink=<?php echo $index; ?>&id=<?php echo $drink_id; ?>" class="bag-remove">Remove</a>
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
</body>
</html>