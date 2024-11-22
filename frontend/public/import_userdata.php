<?php

require_once($_SERVER['APPDIR'] . "/config/setup.php");
require_once($_SERVER['APPDIR'] . "/model/ItemModel.php");

echo $twig->render('import_userdata.twig', [
    'old_host' => $OLD_HOST
]);
