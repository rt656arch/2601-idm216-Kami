<!-- This is an form version of the menu items that stores ordered info into an array -->

<?php
// error_reporting(E_ALL);
// ini_set('display_errors', 1);
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
    <link rel="stylesheet" href="./css/other/noramlize.css">
    <link rel="stylesheet" href="./css/other/global.css">
    <link rel="stylesheet" href="./css/other/main.css">
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
                // echo '<pre>'; print_r($row); echo '</pre>'; die();
                $item_id = $row['id'];
                $item_name = $row['name'];
                $item_description = $row['description'];
                $item_base_price = $row['base_price']; 
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

        <!-- addons -->
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
        <div class="tips">
            <h2>Add a Tip</h2>
            <div class="tip">
                <input type="radio" id="tip1" name="tip-amount" value="1">
                <label for="tip1">$1</label>
            </div>

            <div class="tip">
                <input type="radio" id="tip2" name="tip-amount" value="2">
                <label for="tip2">$2</label>
            </div>

            <div class="tip">
                <input type="radio" id="tip3" name="tip-amount" value="3">
                <label for="tip3">$3</label>
            </div>

            <div class="tip">
                <input type="radio" id="no-tip" name="tip-amount" value="0">
                <label for="no-tip">No Tip</label>
            </div>
        </div>

        <div class="account-section">
            <h2>Create an account</h2>

            <div class="form-group">
                <label for="name">Name</label>
                <input type="text" id="name" name="user_name">
            </div>

            <div class="form-group">
                <label for="number">Phone Number</label>
                <input type="text" id="number" name="user_number" inputmode="numeric" required>
            </div>

            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="user_password">
            </div>
        </div>

        <button type="submit" name="submit_order" class="submit-btn">Order</button>
        <button type="reset" class="reset-btn" onclick="window.location.href=window.location.pathname;">Reset</button>
    </form>
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