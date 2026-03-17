<!-- This is an form version of the menu items that stores ordered info into an array -->

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
    <link rel="stylesheet" href="./css/noramlize.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/main.css">
</head>

<body>
    <header>
        <h1>Team Cloverr</h1>
        <h2>Kami's Food Truck | Order Menu Items | Form</h2>
    </header>
    <form method="POST" action="receipt.php">
        <?php
        if ($result->num_rows > 0) {
            while ($row = $result->fetch_assoc()) {
                $item_id = $row['id'];
                $item_name = $row['name'];
                $item_description = $row['description'];
                $item_base_price = $row['base-price'];
                $image_name = str_replace(' ', '-', $item_name);
                $image_path = "./media/menu-images/" . $image_name . ".jpg";
            ?>
        <div class="menu-item">
            <input type="checkbox" 
                name="items[]" 
                id="item-<?php echo $item_id; ?>" 
                value="<?php echo $item_id; ?>" 
                class="item-checkbox"/>

            <label for="item-<?php echo $item_id; ?>" class="menu-item-info"> 
                <div class="item-info">
                    <h4 class="item-name"><?php echo htmlspecialchars($item_name)?></h4>
                    <p class="item-price">$<?php echo htmlspecialchars($item_base_price)?></p>
                    <p class="item-desc"><?php echo htmlspecialchars($item_description)?></p>
                </div>
                <div class="item-img">
                    <img src="<?php echo htmlspecialchars($image_path)?>" 
                        alt="Picture of <?php echo htmlspecialchars($item_name)?>" 
                        style="width: 200px">
                </div>
            </label>

        <!-- add-ons -->
        <?php
            $stmt_addons = $connection->prepare("SELECT id, name, price FROM add_on_items WHERE item_id = ?");
            $stmt_addons->bind_param("i", $item_id);
            $stmt_addons->execute();
            $result_addons = $stmt_addons->get_result();

            if ($result_addons->num_rows > 0) {
                echo '<div class="menu-item-addons">';

                while ($addon = $result_addons->fetch_assoc()) {
                    ?>
                    <label>
                        <input type="checkbox" 
                            name="order_items[<?php echo $item_id; ?>][]" 
                            value="<?php echo $addon['id']; ?>">
                        <?php echo htmlspecialchars($addon['name']); ?>
                        (+$<?php echo number_format($addon['price'], 2); ?>)
                    </label><br>
                    <?php
                }

                echo '</div>';
            }
            ?>
        </div>
    <?php
        }
    }
    ?>
        <button type="submit" name="submit_order" class="submit-btn">Order</button>
        <button type="reset" class="reset-btn" onclick="window.location.href=window.location.pathname;">Reset</button>
    </form>

    <h2>Your Order</h2>
    <div class="receipt-container">
        
    <?php
        if (isset($_POST['submit_order']) && !empty($_POST['items'])) {

        $subtotal = 0;
        $sales_tax_rate = 0.08;

        $items = $_POST['items'];
        $addons = $_POST['order_items'] ?? [];

        foreach ($items as $item_id) {
            // print item info
            $stmt = $connection->prepare("SELECT name, description, `base-price` FROM menu_items WHERE id = ?");
            $stmt->bind_param("i", $item_id);
            $stmt->execute();
            $result_item = $stmt->get_result();

            if ($row = $result_item->fetch_assoc()) {

                $item_name = $row['name'];
                $item_price = $row['base-price'];

                echo "<div class='order-item'>";
                echo "<p><strong>" . htmlspecialchars($item_name) . "</strong></p>";
                echo "<p>$" . number_format($item_price, 2) . "</p>";

                $subtotal += $item_price;

                // print add on info
                if (!empty($addons[$item_id])) {
                    $selected_addons = $addons[$item_id];

                    $placeholders = implode(',', array_fill(0, count($selected_addons), '?'));
                    $types = str_repeat('i', count($selected_addons));

                    $stmt_addon = $connection->prepare(
                        "SELECT name, price FROM add_on_items WHERE id IN ($placeholders)"
                    );

                    $stmt_addon->bind_param($types, ...$selected_addons);
                    $stmt_addon->execute();
                    $result_addon = $stmt_addon->get_result();

                    while ($addon_row = $result_addon->fetch_assoc()) {

                        echo "<p class='addon'>+ " 
                            . htmlspecialchars($addon_row['name']) 
                            . " ($" . number_format($addon_row['price'], 2) . ")</p>";

                        $subtotal += $addon_row['price'];
                    }
                }
                echo "</div>";
            }
        }

    echo "<h3>Subtotal: $" . number_format($subtotal, 2) . "</h3>";

    $total_tax = $subtotal * $sales_tax_rate;
    $total = $subtotal + $total_tax;

    echo "<h3>Tax: $" . number_format($total_tax, 2) . "</h3>";
    echo "<h3>Total: $" . number_format($total, 2) . "</h3>";
    }
    else {
        echo "<p>Your order is currently empty</p>";
    }

    $connection->close();
    ?>
    </div>

    <div id="bottom-anchor"></div>

    <script>
        if (localStorage.getItem("scrollToBottom") === "true") {
            const anchor = document.getElementById("bottom-anchor");
            if (anchor) {
                anchor.scrollIntoView({ behavior: "instant" });
            }
            localStorage.removeItem("scrollToBottom");
        }

        document.querySelector(".reset-btn").addEventListener("click", function () {
            localStorage.setItem("scrollToBottom", "true");
        });
        document.querySelector(".submit-btn").addEventListener("click", function () {
            localStorage.setItem("scrollToBottom", "true");
        });
    </script>
</body>
</html>