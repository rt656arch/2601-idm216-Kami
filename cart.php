<?php
require 'db.php';

if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
}

// ─── ADD (GET) - appetizers and drinks from menu.php ────────────────────────
if (isset($_GET['item_id'])) {
    $_SESSION['cart'][] = [
        'item_id'    => (int)$_GET['item_id'],
        'item_name'  => $_GET['item_name'],
        'base_price' => (float)$_GET['price'],
        'qty'        => 1,
        'addons'     => [],
        'sides'      => [],
        'drinks'     => [],
    ];
    header('Location: bag.php');
    exit;
}

// ─── ADD (POST) - entrees from menu-item.php ─────────────────────────────────
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
    $_SESSION['cart'][] = [
        'item_id'    => (int)$_POST['item_id'],
        'item_name'  => $_POST['item_name'],
        'base_price' => (float)$_POST['base_price'],
        'qty'        => max(1, (int)$_POST['qty']),
        'addons'     => $_POST['addons'] ?? [],
        'sides'      => $_POST['sides']  ?? [],
        'drinks'     => $_POST['drinks'] ?? [],
    ];
    header('Location: bag.php');
    exit;
}

if (isset($_GET['remove'])) {
  $index = (int)$_GET['remove'];
  if (isset($_SESSION['cart'][$index])) {
    array_splice($_SESSION['cart'], $index, 1);
  }
  header('Location: bag.php');
  exit;
}

if (isset($_GET['remove_side'])) {
  $index = (int)$_GET['remove_side'];
  $id    = (int)$_GET['id'];

  if (isset($_SESSION['cart'][$index])) {
    $_SESSION['cart'][$index]['sides'] = array_values(
      array_filter($_SESSION['cart'][$index]['sides'], fn($s) => (int)$s !== $id)
    );
    unset($_SESSION['cart'][$index]['side_qtys'][$id]); // clean up orphaned qty
  }

  header('Location: bag.php');
  exit;
}

if (isset($_GET['remove_drink'])) {
  $index = (int)$_GET['remove_drink'];
  $id    = (int)$_GET['id'];

  if (isset($_SESSION['cart'][$index])) {
    $_SESSION['cart'][$index]['drinks'] = array_values(
      array_filter($_SESSION['cart'][$index]['drinks'], fn($d) => (int)$d !== $id)
    );
    unset($_SESSION['cart'][$index]['drink_qtys'][$id]); // clean up orphaned qty
  }

  header('Location: bag.php');
  exit;
}

if (isset($_GET['update_qty'])) {
  $index   = (int)$_GET['update_qty'];
  $newQty  = max(1, (int)$_GET['qty']);
  $sideId  = isset($_GET['side_id'])  ? (int)$_GET['side_id']  : null;
  $drinkId = isset($_GET['drink_id']) ? (int)$_GET['drink_id'] : null;

  if (isset($_SESSION['cart'][$index])) {
    if ($sideId) {
      // sides store as array of IDs so qty is shared — 
      // to support independent qty, sides need their own qty tracking
      $_SESSION['cart'][$index]['side_qtys'][$sideId] = $newQty;
    } elseif ($drinkId) {
      $_SESSION['cart'][$index]['drink_qtys'][$drinkId] = $newQty;
    } else {
      $_SESSION['cart'][$index]['qty'] = $newQty;
    }
  }
  exit;
}