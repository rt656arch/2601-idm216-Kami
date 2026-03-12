<?php 

function get_category_items($category) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM menu_items WHERE category = ?");
    $stmt->bind_param('s', $category);
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $appetizers = $result->fetch_assoc();
        return $appetizers;
    } else {
        return NULL;
    }
}

function get_appetizers() {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM appetizer_items");
    if($stmt->execute()) {
        $result = $stmt->get_result();
        $appetizers = $result->fetch_all(MYSQLI_ASSOC);
        return $appetizers;
    } else {
        return NULL;
    }
}

function get_entrees() {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM entree_items");
    if($stmt->execute()) {
        $executed = $stmt->get_result();
        $result = $executed->fetch_all(MYSQLI_ASSOC);
        return $result;
    } else {
        return NULL;
    }
}

function get_drinks() {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM drink_items");
    if($stmt->execute()) {
        $executed = $stmt->get_result();
        $result = $executed->fetch_all(MYSQLI_ASSOC);
        return $result;
    } else {
        return NULL;
    }
}

function get_addons($item_id) {
    global $connection;

    $stmt = $connection->prepare("SELECT * FROM add_on_items WHERE item_id = ?");
    $stmt->bind_param('i', $item_id);
    if($stmt->execute()) {
        $result = $stmt->get_result();
        return $result->fetch_all(MYSQLI_ASSOC);
    } else {
        return NULL;
    }
}

function add_item_to_order($order_info) {
    global $connection;

    
}

?>