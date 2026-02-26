<?php
require 'db.php';

if (isset($_POST['submit_order']) && !empty($_POST['items'])) {

    $sales_tax_rate = 0.08;
    $items   = $_POST['items'];
    $addons  = $_POST['order_items'] ?? [];
    $subtotal = 0;

    // --- 1. Create the order (we'll update totals after) ---
    $stmt = $connection->prepare(
        "INSERT INTO orders (subtotal, tax, total, created_at) VALUES (0, 0, 0, NOW())"
    );
    $stmt->execute();
    $order_id = $connection->insert_id;

    // --- 2. Loop items and insert order_items rows ---
    foreach ($items as $item_id) {

        $stmt = $connection->prepare(
            "SELECT name, `base-price` FROM menu_items WHERE id = ?"
        );
        $stmt->bind_param("i", $item_id);
        $stmt->execute();
        $row = $stmt->get_result()->fetch_assoc();

        if (!$row) continue;

        $item_price = $row['base-price'];
        $subtotal  += $item_price;

        // Insert the menu item as an order_item
        $stmt = $connection->prepare(
            "INSERT INTO order_items (order_id, menu_item_id, item_name, price, type)
             VALUES (?, ?, ?, ?, 'item')"
        );
        $stmt->bind_param("iisd", $order_id, $item_id, $row['name'], $item_price);
        $stmt->execute();

        // --- 3. Handle add-ons for this item ---
        if (!empty($addons[$item_id])) {
            $selected_addons = $addons[$item_id];
            $placeholders    = implode(',', array_fill(0, count($selected_addons), '?'));
            $types           = str_repeat('i', count($selected_addons));

            $stmt_addon = $connection->prepare(
                "SELECT id, name, price FROM add_on_items WHERE id IN ($placeholders)"
            );
            $stmt_addon->bind_param($types, ...$selected_addons);
            $stmt_addon->execute();
            $result_addon = $stmt_addon->get_result();

            while ($addon = $result_addon->fetch_assoc()) {
                $subtotal += $addon['price'];

                $stmt = $connection->prepare(
                    "INSERT INTO order_items (order_id, menu_item_id, item_name, price, type)
                     VALUES (?, ?, ?, ?, 'addon')"
                );
                $stmt->bind_param("iisd", $order_id, $addon['id'], $addon['name'], $addon['price']);
                $stmt->execute();
            }
        }
    }

    // --- 4. Update the order with real totals ---
    $tax   = $subtotal * $sales_tax_rate;
    $total = $subtotal + $tax;

    $stmt = $connection->prepare(
        "UPDATE orders SET subtotal = ?, tax = ?, total = ? WHERE id = ?"
    );
    $stmt->bind_param("dddi", $subtotal, $tax, $total, $order_id);
    $stmt->execute();

    $connection->close();

    // --- 5. Redirect to receipt page ---
    header("Location: receipt.php?order_id=" . $order_id);
    exit();
}
?>