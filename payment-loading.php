<?php
$order_id = (int)($_GET['order_id'] ?? 0);
if ($order_id <= 0) { header('Location: menu.php'); exit; }
?>

<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0, viewport-fit=cover">
<link rel="stylesheet" href="css/_base.css">
<link rel="stylesheet" href="css/_preloader.css">
<link rel="stylesheet" href="css/_responsive.css">
<style>
  #overlay {
    position: absolute;
    top: 0; left: 0;
    width: 100%;
    height: 100vh;
    background: #F1E7DB;
    z-index: 999;
    pointer-events: none;
    opacity: 0;
    transition: opacity 0.35s ease;
  }
</style>
</head>

<body style="overflow: hidden; height: 100vh;">
<main style="overflow: hidden; padding-bottom: 0; position: relative;">
  <div class="preloader payment">
    <img src="images/payment-preloader.gif" alt="Processing payment...">
  </div>
  <div id="overlay"></div>
</main>

<script>
  setTimeout(() => {
    const overlay = document.getElementById('overlay');
    overlay.style.opacity = '1';
    window.location.href = 'home-receipt.php?order_id=<?php echo $order_id; ?>';
  }, 1500);
</script>
</body>
</html>