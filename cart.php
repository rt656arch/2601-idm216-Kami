<?php
session_start();

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
  }

  header('Location: bag.php');
  exit;
}

if ($_SERVER['REQUEST_METHOD'] === 'POST' && !empty($_POST['item_id'])) {
  if (!isset($_SESSION['cart'])) {
    $_SESSION['cart'] = [];
  }

  $_SESSION['cart'][] = [
    'item_id'    => (int)$_POST['item_id'],
    'item_name'  => $_POST['item_name'],
    'base_price' => (float)$_POST['base_price'],
    'qty'        => max(1, (int)$_POST['qty']),
    'addons'     => $_POST['addons']  ?? [],
    'sides'      => $_POST['sides']   ?? [],
    'drinks'     => $_POST['drinks']  ?? [],
  ];

}

header('Location: menu.php');
exit;