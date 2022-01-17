<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

ob_start();

header('Content-Type: application/json; charset=utf-8');

if (filter_input(INPUT_GET, 'item_id', FILTER_VALIDATE_INT)) {
    $current_item_id = (int)$_GET['item_id'];
    if (!isValidItemId($current_item_id)) {
        $current_item_id = $default_item_id;
    }
} else {
    echo '{"success":false}';
    die();
}

$stock_data = '{"success":true, "data":' . json_encode(getItemChartData($current_item_id)) . ', "bid_ask":' . json_encode(getItemBidAskData($current_item_id)) . '}';
echo $stock_data;

ob_end_flush();