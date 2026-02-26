<?php
require 'db.php';

if (isset($_POST['submit_order']) && !empty($_POST['items'])) {

    $sales_tax_rate = 0.08;
    $items   = $_POST['items'];
    $addons  = $_POST['order_items'] ?? [];
    $tip = $_POST['tip-amount'];
    $user_name = $_POST['user_name'];
    $_SESSION['user_name'] = $user_name;
    $subtotal = 0;

    $session_id = $_SESSION['db_session_id'];

    $stmt = $connection->prepare(
        "INSERT INTO orders (session_id, subtotal, tax, tip, total, created_at) VALUES (?, 0, 0, 0, 0, NOW())"
    );
    $stmt->bind_param("s", $session_id);
    $stmt->execute();
    $order_id = $connection->insert_id;

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

        $stmt = $connection->prepare(
            "INSERT INTO order_items (order_id, menu_item_id, item_name, price, type)
             VALUES (?, ?, ?, ?, 'item')"
        );
        $stmt->bind_param("iisd", $order_id, $item_id, $row['name'], $item_price);
        $stmt->execute();

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

    $tax   = $subtotal * $sales_tax_rate;
    $total = $subtotal + $tax + $tip;

    $stmt = $connection->prepare(
        "UPDATE orders SET subtotal = ?, tax = ?, tip = ?, total = ? WHERE id = ?"
    );
    $stmt->bind_param("ddddi", $subtotal, $tax, $tip, $total, $order_id);
    $stmt->execute();

    header("Location: receipt.php?order_id=" . $order_id);
    exit();
}

if (!isset($_GET['order_id'])) {
    header("Location: menu.php");
    exit();
}

$user_name = $_SESSION['user_name'] ?? "Guest";
$order_id = (int) $_GET['order_id'];

$stmt = $connection->prepare("SELECT * FROM orders WHERE id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$order = $stmt->get_result()->fetch_assoc();

if (!$order) die("Order not found.");

$stmt = $connection->prepare("SELECT * FROM order_items WHERE order_id = ?");
$stmt->bind_param("i", $order_id);
$stmt->execute();
$line_items = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <title>Receipt</title>
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/global.css">
    <link rel="stylesheet" href="./css/main.css">
    <link rel="stylesheet" href="./css/receipt.css">
</head>
<body>
    <h2 style="font-family: var(--body-font)"><?= $user_name ?>, Your Receipt</h2>
    <h1>Order #<?= $order_id ?></h1>

    <div class="receipt-container">
    <?php foreach ($line_items as $line): ?>
        <?php if ($line['type'] === 'item'): ?>
            <div class="top-border">
                <div class="order-item">
                    <span class="order-item-name"><?= htmlspecialchars($line['item_name']) ?></span>
                    <span class="order-item-price">$<?= number_format($line['price'], 2) ?></span>
                </div>
            </div>
        <?php else: ?>
            <div class="bottom-border">
                <div class="addon">
                    <span class="addon-name">+ <?= htmlspecialchars($line['item_name']) ?></span>
                    <span class="addon-price">$<?= number_format($line['price'], 2) ?></span>
                </div>
            </div>
        <?php endif; ?>
    <?php endforeach; ?>

        <hr>
        <div class="receipt-totals">
            <p>Subtotal: <span>$<?= number_format($order['subtotal'], 2) ?></span></p>
            <p>Tax: <span>$<?= number_format($order['tax'], 2) ?></span></p>
            <p>Tip: <span>$<?= number_format($order['tip'], 2) ?></span></p>
            <h3>Total: <span>$<?= number_format($order['total'], 2) ?></span></h3>
        </div>
    </div>

    <a class="back-to-menu" href="main.php">← Back to Menu</a>
</body>
</html>