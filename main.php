<?php
require 'db.php';

if (!isset($result)) {
    $stmt = $connection->prepare("SELECT * FROM menu_items");
    $stmt->execute();
    $result = $stmt->get_result();
    $result_count = $result->num_rows;
}

$connection->close();
?>

<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Document</title>
    <link rel="stylesheet" href="./css/style.css">
    <link rel="stylesheet" href="./css/noramlize.css">
</head>
<body>