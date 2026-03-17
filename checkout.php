<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require 'db.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: bag.php');
  exit;
}

$cart        = $_SESSION['cart'] ?? [];
$tip         = (float)($_POST['tip'] ?? 0);
$pickup_time = $_POST['pickup_time'] ?? '11:20am';
$sales_tax   = 0.08;
$subtotal    = 0;
$session_id  = $_SESSION['db_session_id'];

// Create the order row
$stmt = $connection->prepare(
  "INSERT INTO kami_orders (session_id, subtotal, tax, tip, pickup_time, total, created_at)
   VALUES (?, 0, 0, 0, ?, 0, NOW())"
);
$stmt->bind_param("ss", $session_id, $pickup_time);
$stmt->execute();
$order_id = $connection->insert_id;

foreach ($cart as $entry) {
  $item_id    = $entry['item_id'];
  $item_price = $entry['base_price'];
  $qty = (int)$entry['qty'];
  $subtotal  += $item_price * $qty;

  // Insert main item
  $stmt = $connection->prepare(
    "INSERT INTO kami_order_items (order_id, menu_item_id, item_name, price, type, quantity)
     VALUES (?, ?, ?, ?, 'item', ?)"
  );
  $stmt->bind_param("iisdi", $order_id, $item_id, $entry['item_name'], $item_price, $qty);
  $stmt->execute();

  // Addons
  if (!empty($entry['addons'])) {
    $ids          = $entry['addons'];
    $placeholders = implode(',', array_fill(0, count($ids), '?'));
    $types        = str_repeat('i', count($ids));
    $stmt         = $connection->prepare("SELECT id, name, price FROM add_on_items WHERE id IN ($placeholders)");
    $stmt->bind_param($types, ...$ids);
    $stmt->execute();
    $rows = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

    foreach ($rows as $addon) {
      $subtotal += $addon['price'] * $qty;
      $stmt = $connection->prepare(
        "INSERT INTO kami_order_items (order_id, menu_item_id, item_name, price, type, quantity)
         VALUES (?, ?, ?, ?, 'addon', ?)"
      );
      $stmt->bind_param("iisdi", $order_id, $addon['id'], $addon['name'], $addon['price'], $qty);
      $stmt->execute();
    }
  }

  // Sides
    foreach ($entry['sides'] ?? [] as $side_id) {
      $side_qty  = (int)($entry['side_qtys'][$side_id] ?? $entry['qty']); // use independent qty
      $stmt = $connection->prepare("SELECT name, `base-price` FROM appetizer_items WHERE id = ?");
      $stmt->bind_param("i", $side_id);
      $stmt->execute();
      $side      = $stmt->get_result()->fetch_assoc();
      $subtotal += $side['base-price'] * $side_qty;

      $stmt = $connection->prepare(
        "INSERT INTO kami_order_items (order_id, menu_item_id, item_name, price, type, quantity)
        VALUES (?, ?, ?, ?, 'side', ?)"
      );
      $stmt->bind_param("iisdi", $order_id, $side_id, $side['name'], $side['base-price'], $side_qty);
      $stmt->execute();
    }

    // Drinks
    foreach ($entry['drinks'] ?? [] as $drink_id) {
      $drink_qty = (int)($entry['drink_qtys'][$drink_id] ?? $entry['qty']); // use independent qty
      $stmt = $connection->prepare("SELECT name, `base-price` FROM drink_items WHERE id = ?");
      $stmt->bind_param("i", $drink_id);
      $stmt->execute();
      $drink     = $stmt->get_result()->fetch_assoc();
      $subtotal += $drink['base-price'] * $drink_qty;

      $stmt = $connection->prepare(
        "INSERT INTO kami_order_items (order_id, menu_item_id, item_name, price, type, quantity)
        VALUES (?, ?, ?, ?, 'drink', ?)"
      );
      $stmt->bind_param("iisdi", $order_id, $drink_id, $drink['name'], $drink['base-price'], $drink_qty);
      $stmt->execute();
    }
}

$tax   = round($subtotal * $sales_tax, 2);
$total = round($subtotal + $tax + $tip, 2);

$stmt = $connection->prepare(
  "UPDATE kami_orders SET subtotal = ?, tax = ?, tip = ?, total = ? WHERE id = ?"
);
$stmt->bind_param("ddddi", $subtotal, $tax, $tip, $total, $order_id);
$stmt->execute();

unset($_SESSION['cart']);

header("Location: payment-loading.php?order_id=" . $order_id);

exit;