<?php
require 'db.php';
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/noramlize.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/main.css">
</head>
<body>
    <h2>Your Order</h2>
    <div class="receipt-container">
        
    <?php
        if (isset($_POST['submit_order']) && !empty($_POST['items'])) {

    $subtotal = 0;
    $sales_tax_rate = 0.08;

    $items = $_POST['items'];
    $addons = $_POST['order_items'] ?? [];

    foreach ($items as $item_id) {

        // Get item info by ID (NOT name anymore)
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

            // HANDLE ADDONS FOR THIS ITEM
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
</body>
</html>