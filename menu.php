<?php
require 'db.php';
require './lib/functions.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
  <title>Menu | Kami Food Truck</title>
  <link href="https://fonts.googleapis.com/css2?family=Black+Han+Sans&family=DM+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="style.css">
</head>

<body>
  <main>
    <div class="menu-search">
      <br><br>
    </div>

    <div class="menu-tabs">
      <br><br>
      <a href="#" class="active" data-tab="appetizers">appetizers</a>
      <span>|</span>
      <a href="#" data-tab="entrees">entrees</a>
      <span>|</span>
      <a href="#" data-tab="drinks">drinks</a>
    </div>
    <div class="grid-white"></div>

<div class="menu-list">
  <!-- ═══ APPETIZERS ═══ -->
  <div class="menu-section active" id="appetizers">
    <?php 
    $appetizers = get_appetizers();
    if($appetizers && (count($appetizers) > 0)) :
      foreach($appetizers as $row) :
        $menu_item_link = "menu-item.php?id=" . $row['id'];
        $item_id = (int)$row['id'];
        $item_korean_name = $row['korean-name'];
        $item_name = $row['name'];
        $item_base_price = (float)$row['base-price'];
        $image_name = str_replace(' ', '-', $row['name']);
        $image_path = "./media/menu-images/" . $image_name . ".jpg";
    ?>
    <div class="menu-card">
      <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>">
      <div class="menu-card-body">
          <h3><?php echo htmlspecialchars($item_korean_name)?></h3>
        <div class="menu-card-name">
          <p class="bold"><?php echo htmlspecialchars($item_name)?></p>
          <span class="bold"><h4>$<?php echo htmlspecialchars($item_base_price)?></h4></span>
        </div>
        <div class="menu-card-btns">
          <a href="bag.html" class="pill">ADD TO BAG</a>
        </div>
      </div>
    </div>
    <?php
      endforeach;
    endif;
    // $connection->close();
    ?>
  </div>

  <!-- ═══ ENTREES ═══ -->
<div class="menu-section" id="entrees">
  <?php
    $entrees = get_entrees();
    if($entrees && (count($entrees) > 0)) :
      foreach($entrees as $row) :
        $menu_item_link = "menu-item.php?id=" . $row['id'];
        
        $item_id = $row['id'];
        $item_korean_name = $row['korean-name'];
        $item_name = $row['name'];
        $item_base_price = $row['base-price'];
        $image_name = str_replace(' ', '-', $row['name']);
        $image_path = "./media/menu-images/" . $image_name . ".jpg";
  ?>
  <div class="menu-card">
      <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>">
      <div class="menu-card-body">
          <h3><?php echo htmlspecialchars($item_korean_name)?></h3>
        <div class="menu-card-name">
          <p class="bold"><?php echo htmlspecialchars($item_name)?></p>
          <span class="bold"><h4>$<?php echo htmlspecialchars($item_base_price)?></h4></span>
        </div>
        <div class="menu-card-btns">
          <a class="pill" href= <?php echo "/menu-item.php?id=" . htmlspecialchars($item_id)?> >Customize</a>
        </div>
      </div>
    </div>
    <?php
    endforeach;
    endif;
    // $connection->close();
    ?>
</div>

<!-- ═══ DRINKS ═══ -->
  <div class="menu-section" id="drinks">
    <?php
    $drinks = get_drinks();
    if($drinks && (count($drinks) > 0)) :
      foreach($drinks as $row) :
        $menu_item_link = "menu-item.php?id=" . $row['id'];
        
        $item_id = $row['id'];
        $item_korean_name = $row['korean-name'];
        $item_name = $row['name'];
        $item_base_price = $row['base-price'];
        $image_name = str_replace(' ', '-', $row['name']);
        $image_path = "./media/menu-images/" . $image_name . ".jpg";
  ?>
  <div class="menu-card">
      <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>">
      <div class="menu-card-body">
          <h3><?php echo htmlspecialchars($item_korean_name)?></h3>
        <div class="menu-card-name">
          <p class="bold"><?php echo htmlspecialchars($item_name)?></p>
          <span class="bold"><h4>$<?php echo htmlspecialchars($item_base_price)?></h4></span>
        </div>
        <div class="menu-card-btns">
          <a href="bag.html" class="pill">ADD TO BAG</a>
        </div>
      </div>
    </div>
    <?php
    endforeach;
    endif;
    // $connection->close();
    ?>
  </div>
  </div>

<br><br>
<!-- Bottom Nav -->
<nav>
  <div class="active">
    <img src="images/navigation-bar-menu.png" alt="Menu">
    <span>Menu</span>
  </div>

  <div>
    <img src="images/navigation-bar-home.png" alt="Home">
    <span>Home</span>
  </div>

  <div>
    <img src="images/navigation-bar-bag.png" alt="Bag">
    <span>Bag</span>
  </div>
</nav>
</main>
</body>
<!-- </script> -->
<script>
    /* -- Tab switching --------------------------- */
    const tabs     = document.querySelectorAll('.menu-tabs a');
    const sections = document.querySelectorAll('.menu-section');

    tabs.forEach(tab => {
      tab.addEventListener('click', function(e) {
        e.preventDefault();
        e.stopPropagation();

        tabs.forEach(t => t.classList.remove('active'));
        sections.forEach(s => s.classList.remove('active'));

        this.classList.add('active');

        const target = document.getElementById(this.dataset.tab);
        if (target) {
          target.classList.add('active');
        } else {
          console.warn('No section found for tab:', this.dataset.tab);
        }

        window.scrollTo(0, 0);
      });
    });
  </script>
</html>
