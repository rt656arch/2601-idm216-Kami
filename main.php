<?php
require 'db.php';

if (!isset($result)) {
    $stmt = $connection->prepare("SELECT * FROM menu_items");
    $stmt->execute();
    $result = $stmt->get_result();
}
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Menu Items Page | Form </title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/noramlize.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>
    <header>
        <h1>Team Cloverr</h1>
        <h2>Kami's Food Truck | Order Menu Items | Form</h2>
    </header>
    <form method="POST" action="">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {

            $item_id = $row['id'];
            $item_name = $row['name'];
            $item_description = $row['description'];
            $item_base_price = $row['base-price'];
            $image_name = str_replace(' ', '-', $row['name']);
            $image_path = "./media/menu-images/high-quality/" . $image_name . ".jpg";
        ?>
            <div class="menu-item">
                <input type="checkbox" name="items[]" id="<?php echo $item_name?>" value="<?php echo $item_name?>" class="item-checkbox"/>
                <label for="<?php echo $item_name?>" class="menu-item-info"> 
                    <div class="item-info">
                        <p class="item-id"><?php echo htmlspecialchars($item_id)?></p>
                        <h4 class="item-name"><?php echo htmlspecialchars($item_name)?></h4>
                        <p class="item-price">$<?php echo htmlspecialchars($item_base_price)?></p>
                        <p class="item-desc"><?php echo htmlspecialchars($item_description)?></p>
                    </div>
                    <div class="item-img">
                        <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>" style="width: 200px">
                    </div>
                </label>
            </div>
        <?php }
            } ?>
        <button type="submit" name="submit_order" class="submit-btn" onclick="window.location.href=window.location.pathname;">Order</button>
        <button type="reset" class="reset-btn" onclick="window.location.href=window.location.pathname;">Reset</button>
    </form>

    <h2>Your Order</h2>
    <?php
        if (isset($_POST['submit_order']) && !empty($_POST['items'])) {
            $total = 0;
            foreach ($_POST['items'] as $selected_id) {
                $stmt = $connection->prepare("SELECT name, description, `base-price` FROM menu_items WHERE name = ?");
                $stmt->bind_param("s", $selected_id);
                $stmt->execute();
                $result_item = $stmt->get_result();

                if ($row = $result_item->fetch_assoc()) {

                    $item_name = $row['name'];
                    $item_price = $row['base-price'];
                    $item_description = $row['description'];

                    echo "<div class='order-item'>";
                    echo "<p class='item-name'><strong>" . htmlspecialchars($item_name) . "</strong></p>";
                    echo "<p class='item-price'>$" . htmlspecialchars($item_price) . "</p>";
                    echo "</div>";

                    $total += $item_price;
                }
            }
            echo "<h3>Total: $" . number_format($total, 2) . "</h3>";
        }
        else {
            echo "<p>Your order is currently empty</p>";
            }

        // echo "<h3>Debug Items Array:</h3>";
        //     if (!empty($_POST['items'])) {
        //         echo "<pre>";
        //         print_r($_POST['items']);
        //         echo "</pre>";
        //     } else {
        //         echo "<p>items[] is empty</p>";
        //     }

        $connection->close();
    ?>

    <div id="bottom-anchor"></div>

    <script>
// If we stored scroll position, go there
if (localStorage.getItem("scrollToBottom") === "true") {
    const anchor = document.getElementById("bottom-anchor");
    if (anchor) {
        anchor.scrollIntoView({ behavior: "instant" });
    }
    localStorage.removeItem("scrollToBottom");
}

// When reset button is clicked, remember to scroll
document.querySelector(".reset-btn").addEventListener("click", function () {
    localStorage.setItem("scrollToBottom", "true");
});
document.querySelector(".submit-btn").addEventListener("click", function () {
    localStorage.setItem("scrollToBottom", "true");
});
</script>
</body>
</html>