<?php
require 'db.php';

if (!isset($result)) {
    $stmt = $connection->prepare("SELECT * FROM entree_items");
    $stmt->execute();
    $result = $stmt->get_result();
    $result_count = $result->num_rows;
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Template</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/noramlize.css">
</head>
<body>
    <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

            $item_korean_name = $row['korean-name'];
            $item_name = $row['name'];
            $item_base_price = $row['base-price'];
            $image_name = str_replace(' ', '-', $row['name']);
            $image_path = "./media/menu-images/high-quality/" . $image_name . ".jpg";
    ?>
    <div class="menu-item-card">
        <div class="menu-item-img">
            <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>">
        </div>
        <div class="menu-item-info">
            <div class="menu-item-text">
                <div class="menu-item-name">
                    <h3><?php echo htmlspecialchars($item_korean_name)?></h3>
                    <p><?php echo htmlspecialchars($item_name)?></p>
                </div>
                <div class="menu-item-price">
                    <h4>$<?php echo htmlspecialchars($item_base_price)?></h4>
                </div>
            </div>
            <div class="menu-item-actions">
                <button class="customize-button"></button>    
                <button class="add-to-cart-button"></button>    
            </div>
        </div>
    </div>
    <?php }
        } else {
            echo "<p>No menu items found.</p>";
        }?>
</body>
</html>