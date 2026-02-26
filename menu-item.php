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
  <title><?php echo htmlspecialchars($name)?></title>
  <link rel="stylesheet" href="style.css">
</head>
<body>
  <div class="shell">
    <div>
      <div class="hero">
        <div class="hero-image">
          <img src="images/tteokbokki-full.png" alt="Tteokbokki">
        </div> 

        <div class="back-button">
            <a href="home.html" >&#10094;</a>
        </div>

        <div class="favorite-button">★</div>
      </div>

      <div>
        <p class="muted">떡볶이</p>
        <div class="item">
          <h1>Tteokbokki</h1>
          <span class="bold">$10</span>
        </div>
        <p class="muted">Chewy Korean rice cakes simmered in a spicy-sweet gochujang sauce, served with fish cakes and green onions.</p>
        <p class="muted italic">*Contains: Fish, Soy, Sesame</p>

        <div class="section">
          <h3>Add Ons</h3>
          <a href="customize-ramen.html#ramen" class="option clickable" id="ramen">
            <span class="check"></span>
            <span class="bold">Ramen</span>
            <span class="muted">$1</span>
          </a>
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
        </div>

        <div class="section">
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
          <a href="customize-rice.html#rice" class="option clickable" id="rice">
            <span class="check"></span>
            <span class="bold">Small Rice <span class="bold">[5oz]</span></span>
            <span class="muted">$3</span>
          </a>
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
        </div>

        <div class="section">
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
        </div>
      </div>
    </div>

    <div class="sticky">
      <div class="qty">
        <span>−</span>
        <span>1</span>
        <span>+</span>
      </div>
      <a href="bag.html" class="btn">ADD ($10)</a>
    </div>

  </div>
</body>
</html>