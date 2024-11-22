<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

$default_item_id = getDefaultItem(); //SUPER|brie+

if (filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT)) {
    $current_item_id = (int)$_GET['item_id'];
    if (!isValidItemId($current_item_id)) {
        $current_item_id = $default_item_id;
    }
} else {
    $current_item_id = $default_item_id;
}

$current_item_name = getItemName($current_item_id);

echo $twig->render('portfolio.twig', [
    'current_item_id' => $current_item_id,
    'current_item_name' => $current_item_name,
    'item_metadata' => getAllItemNamesAndLatestPrice(),
    'event_data' => getEvents(),
]);
