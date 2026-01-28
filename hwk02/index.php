<?php
require 'db.php';

$stmt = $connection->prepare("SELECT * FROM menu_items");
$stmt->execute();
$result = $stmt->get_result();
$result_count = $result->num_rows;

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Website</title>
    <link rel="stylesheet" href="./css/normalize.css">
    <link rel="stylesheet" href="./css/style.css">
</head>

<header>
    <h1>Team Cloverr</h1>
    <h2>Kami's Food Truck | Database Display Website</h2>
</header>

<body>
    <h2>Menu Items</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="db-table">
            <thead>
                <tr>
                    <?php
                    while ($field = $result->fetch_field()) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    ?>
                    
                    <th>Image</th>
                </tr>
            </thead>
            <tbody>
                <?php
                $result->data_seek(0);

                while ($row = $result->fetch_assoc()):
                    $image_name = str_replace(' ', '-', $row['name']);
                    $image_path = "../menu-images/dish-bagged-image/" . $image_name . ".png";
                ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>

                        <td class="img-cell">
                            <img src="<?= $image_path ?>" alt="<?= htmlspecialchars($row['name']) ?>">
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>
</body>
</html>