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

    <form method="POST">
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
        <input type="checkbox" name="items[]" id="<?php echo $item_name?>" value="<?php echo $item_name?>"/>
        <label for="item-<?php echo $item_id; ?>">
            <div class="item-info">
                <p><?php echo htmlspecialchars($item_id)?></p>
                <p><?php echo htmlspecialchars($item_name)?></p>
                <p><?php echo htmlspecialchars($item_base_price)?></p>
                <p><?php echo htmlspecialchars($item_description)?></p>
                <img src="<?php echo htmlspecialchars($image_path)?>" alt="Picture of <?php echo htmlspecialchars($item_name)?>" style="width: 200px">
            </div>
    </label>
    <?php }
        } ?>
    <button type="submit" name="submit_order">Order</button>
    </form>

    <?php
        if (isset($_POST['submit_order']) && !empty($_POST['items'])) {

            echo "<h2>Your Order</h2>";

            $total = 0;

            foreach ($_POST['items'] as $selected_id) {

                $stmt = $connection->prepare("SELECT name, description, `base-price` FROM menu_items WHERE id = ?");
                $stmt->bind_param("i", $selected_id);
                $stmt->execute();
                $result_item = $stmt->get_result();

                if ($row = $result_item->fetch_assoc()) {

                    $item_name = $row['name'];
                    $item_price = $row['base-price'];
                    $item_description = $row['description'];

                    echo "<div class='order-item'>";
                    echo "<p><strong>" . htmlspecialchars($item_name) . "</strong></p>";
                    echo "<p>$" . htmlspecialchars($item_price) . "</p>";
                    echo "<p>" . htmlspecialchars($item_description) . "</p>";
                    echo "</div>";

                    $total += $item_price;
                }
            }

            echo "<h3>Total: $" . number_format($total, 2) . "</h3>";
        }

        $connection->close();
        ?>

</head>
<body>