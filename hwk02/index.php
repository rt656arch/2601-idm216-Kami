<?php
require 'db.php';

$stmt = $connection->prepare("SELECT * FROM menu_items");
$stmt->execute();
$result = $stmt->get_result();

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
                    $image_path = "./menu-images/high-quality/" . $image_name . ".jpg";
                ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>

                        <td class="img-cell">
                            <img src="<?= $image_path ?>" alt="Picture of <?=htmlspecialchars($row['name']) ?>">
                        </td>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>

    <?php
        $stmt = $connection->prepare("SELECT * FROM add_on_items");
        $stmt->execute();
        $result = $stmt->get_result();

        $connection->close();
    ?>
    <h2>Add-on Items</h2>
    <?php if ($result && $result->num_rows > 0): ?>
        <table class="db-table">
            <thead>
                <tr>
                    <?php
                    while ($field = $result->fetch_field()) {
                        echo "<th>" . htmlspecialchars($field->name) . "</th>";
                    }
                    ?>
                </tr>
            </thead>
            <tbody>
                <?php while ($row = $result->fetch_assoc()): ?>
                    <tr>
                        <?php foreach ($row as $value): ?>
                            <td><?= htmlspecialchars($value) ?></td>
                        <?php endforeach; ?>
                    </tr>
                <?php endwhile; ?>
            </tbody>
        </table>
    <?php else: ?>
        <p>No records found.</p>
    <?php endif; ?>
</body>
</html>