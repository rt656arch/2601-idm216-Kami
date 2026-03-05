<?php
require 'db.php';

$id = $_GET['id'];
echo htmlspecialchars($id);

if (!isset($result)) {
    $stmt = $connection->prepare("SELECT * FROM entree_items WHERE id = ?");
    $stmt->bind_param("i", $id);
    $stmt->execute();
    $result = $stmt->get_result();
    $item = $result->fetch_assoc();

    $korean_name = $item['korean-name'];
    $name = $item['name'];
    $base_price = '$' . $item['base-price'];
    $description = $item['description'];
    $allergens = 'Contains: ' . $item['allergens'];
    $image_name = str_replace(' ', '-', $item['name']);
    $image_path = "./media/menu-images/" . $image_name . ".jpg";
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<title>Customize | Kami Food Truck</title>
<link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
<link rel="stylesheet" href="style.css">
</head>

<body><main>
<div>
<div class="hero">
<img src="images/tteokbokki-full.png" alt="Tteokbokki">
<a href="home.html">&#10094;</a>
<div>★</div>
</div>

<div class="detail">
<p class="muted detail-label">떡볶이</p>
<div class="detail-title">
<h1>Tteokbokki</h1>
<span class="bold">$10</span>
</div>

<p class="muted detail-desc">Chewy Korean rice cakes simmered in a spicy-sweet gochujang sauce, served with fish cakes and green onions.</p>
<p class="muted italic">*Contains: Fish, Soy, Sesame</p>


<br>
<section>
<h3>Add Ons</h3>
<div class="option clickable" id="ramen">
  <span class="check">✓</span>
  <span class="bold">Ramen</span>
  <span class="muted">$1</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Egg</span>
  <span class="muted">$1</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Cheese</span>
  <span class="muted">$1</span>
</div>
</section>


<br>
<section>
<h3 class="italic">Sides</h3>
<div class="option">
  <span class="check"></span>
  <span class="bold">Fried Rice Cakes</span>
  <span class="muted">$6</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Korean Cheese Dog</span>
  <span class="muted">$5</span>
</div>

<div class="option clickable" id="rice">
  <span class="check">✓</span>
  <span class="bold">Small Rice <span class="bold">[5oz]</span></span>
  <span class="muted">$3</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Large Rice <span class="bold">[10oz]</span></span>
  <span class="muted">$5</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Side Kimchi</span>
  <span class="muted">$1</span>
</div>
</section>


<br>
<section>
<h3>Drinks</h3>
<div class="option">
  <span class="check"></span>
  <span class="bold">Soda</span>
  <span class="muted">$1</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Tea</span>
  <span class="muted">$1</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Coffee</span>
  <span class="muted">$1</span>
</div>

<div class="option">
  <span class="check"></span>
  <span class="bold">Water</span>
  <span class="muted">$1</span>
</div>
</section>
</div>
</div>
<br><br>

<div class="sticky">
<div class="qty">
<span>−</span>
<span>1</span>
<span>+</span>
</div>

<a href="bag.html" class="btn" id="add-btn">ADD ($10)</a>
</div>
</main>
<script>
const ramen_option = document.getElementById('ramen');
const rice_option = document.getElementById('rice');
const add_btn = document.getElementById('add-btn');

let ramen_selected = false;
let rice_selected = false;
const base_price = 10;

function update_price() {
let total = base_price;
  if (ramen_selected) { total = total + 1; }
  if (rice_selected) { total = total + 3; }
add_btn.textContent = 'ADD ($' + total + ')';
}

if (ramen_option) {
  ramen_option.addEventListener('click', function() {
  const check = ramen_option.querySelector('.check');
  check.className = ramen_selected ? 'check' : 'check active';
  ramen_selected = !ramen_selected;
update_price();
});
}

if (rice_option) {
  rice_option.addEventListener('click', function() {
  const check = rice_option.querySelector('.check');
  check.className = rice_selected ? 'check' : 'check active';
  rice_selected = !rice_selected;
update_price();
});
}
</script>
</body>
</html>
