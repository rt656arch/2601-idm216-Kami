    <h3>This should be from the dish bagged image folder</h3>
    <?php
        if ($result->num_rows > 0) {
        while ($row = $result->fetch_assoc()) {
            $image_name = str_replace(' ', '-', $row['name']);
            $image_path = "menu-images/dish-bagged-image/" . $image_name . ".png";
        ?>
                
                <div class="recipe-card">
                    <div class="recipe-img">
                            <img src="<?php echo $image_path; ?>" alt="test">
                        </div>
                        <div class="recipe-name">
                            <h4><?php echo htmlspecialchars($row['name']); ?></h4>
                        </div>
                </div>

            <?php
                    }
                } else {
                    echo "<p>No recipes found.</p>";
                }
            ?>