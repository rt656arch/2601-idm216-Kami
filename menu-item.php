<?php
require 'db.php';
require './lib/functions.php';

$id = isset($_GET['id']) ? (int)$_GET['id'] : 0;
if ($id <= 0) { header('Location: menu.php'); exit; }

$stmt = $connection->prepare("SELECT * FROM entree_items WHERE id = ?");
$stmt->bind_param("i", $id);
$stmt->execute();
$item = $stmt->get_result()->fetch_assoc();
if (!$item) { header('Location: menu.php'); exit; }

$addons = get_addons($id);

$sides = get_appetizers();

$drinks = get_drinks();

$korean_name = $item['korean-name'];
$name        = $item['name'];
$base_price  = (float)$item['base-price'];
$description = $item['description'];
$allergens   = $item['allergens'];
$image_path  = "./media/menu-images/" . str_replace(' ', '-', $name) . ".jpg";

$connection->close();
?>


<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title><?php echo htmlspecialchars($name); ?> | Kami Food Truck</title>
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
<main>
  <div>
    <div class="hero">
      <img src="<?php echo htmlspecialchars($image_path); ?>" alt="<?php echo htmlspecialchars($name); ?>">
      <a href="menu.php">&#10094;</a>
      <div>★</div>
    </div>

    <div class="detail">
      <p class="muted detail-label"><?php echo htmlspecialchars($korean_name); ?></p>
      <div class="detail-title">
        <h1><?php echo htmlspecialchars($name); ?></h1>
        <span class="bold">$<?php echo number_format($base_price, 2); ?></span>
      </div>

      <p class="muted detail-desc"><?php echo htmlspecialchars($description); ?></p>
      <?php if (!empty($allergens)): ?>
        <p class="muted italic">*Contains: <?php echo htmlspecialchars($allergens); ?></p>
      <?php endif; ?>
    <!-- </div> -->
      <br>

  <fieldset>
    <?php if (!empty($addons)): ?>
    <section>
      <h3>Add Ons</h3>
      <?php foreach($addons as $addon): ?>
      <label class="option">
        <input type="checkbox"
              name="addons[]"
              value="<?php echo htmlspecialchars($addon['name']); ?>"
              data-price="<?php echo $addon['price']; ?>">
        <span class="check"></span>
        <span class="bold"><?php echo htmlspecialchars($addon['name']); ?></span>
        <span class="muted">$<?php echo number_format($addon['price'], 2); ?></span>
      </label>
      <?php endforeach; ?>
    </section>
    <?php endif; ?>

    <section>
      <h3>Sides</h3>
      <?php foreach($sides as $side): ?>
      <label class="option">
        <input type="checkbox"
              name="sides[]"
              value="<?php echo htmlspecialchars($side['name']); ?>"
              data-price="<?php echo $side['base-price']; ?>">
        <span class="check"></span>
        <span class="bold"><?php echo htmlspecialchars($side['name']); ?></span>
        <span class="muted">$<?php echo number_format($side['base-price'], 2); ?></span>
      </label>
      <?php endforeach; ?>
    </section>

    <section>
      <h3>Drinks</h3>
      <?php foreach($drinks as $drink): ?>
      <label class="option">
        <input type="checkbox"
              name="drinks[]"
              value="<?php echo htmlspecialchars($drink['name']); ?>"
              data-price="<?php echo $drink['base-price']; ?>">
        <span class="check"></span>
        <span class="bold"><?php echo htmlspecialchars($drink['name']); ?></span>
        <span class="muted">$<?php echo number_format($drink['base-price'], 2); ?></span>
      </label>
      <?php endforeach; ?>
    </section>
  </fieldset>


    </div>
  </div>
  <br><br>

  <div class="sticky">
    <div class="qty">
      <span id="qty-dec">−</span>
      <span id="qty-count">1</span>
      <span id="qty-inc">+</span>
    </div>
    <a href="bag.html" class="btn" id="add-btn">ADD ($<?php echo number_format($base_price, 2); ?>)</a>
  </div>
</main>



<script>
  const BASE_PRICE = <?php echo $base_price; ?>;
  let qty = 1;
  document.querySelectorAll('.option input[type="checkbox"]').forEach(input => {
  input.addEventListener('change', function() {
    const check = this.nextElementSibling; // the .check span
    check.classList.toggle('active', this.checked);
    check.textContent = this.checked ? '✓' : '';
    updatePrice();
  });
});

  /* ── Quantity ── */
  document.getElementById('qty-dec').addEventListener('click', () => {
    if (qty > 1) { qty--; updatePrice(); }
  });
  document.getElementById('qty-inc').addEventListener('click', () => {
    qty++;
    updatePrice();
  });

  function updatePrice() {
    document.getElementById('qty-count').textContent = qty;

    let addons = 0;
    document.querySelectorAll('.option input[type="checkbox"]:checked').forEach(input => {
      addons += parseFloat(input.dataset.price) || 0;
    });

    const total = (BASE_PRICE + addons) * qty;
    document.getElementById('add-btn').textContent =
      'ADD ($' + total.toFixed(2) + ')';
}
</script>
</body>
</html>