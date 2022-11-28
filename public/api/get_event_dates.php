<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");

ob_start();
header('Content-Type: application/json; charset=utf-8');
header('Access-Control-Allow-Origin: *');

$newUrl = "https://$API_HOST/events";
$json = json_encode([
    'message' => "Moved to $newUrl",
    'success' => false
]);

echo $json;
die();