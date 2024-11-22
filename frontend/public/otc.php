<?php
require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

echo $twig->render('otc.twig', [
    'event_data' => getEvents(),
]);
